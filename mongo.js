function recreate(src, pipeline, index, dest, merge) {
	let start = Date.now();
	if(merge) {
		last_op = {
			$merge: {
				into: 'tmp'
			}
		};
	} else {
		last_op = {
			$out: 'tmp'
		};
	}
	pipeline.push(last_op);
	src.aggregate(pipeline, {
		allowDiskUse: true
	});
	db.tmp.ensureIndex(index);
	if(dest != 'tmp') {
		db.tmp.renameCollection(dest, true);
	}
	print('recreated ' + dest + ' in ' + (Date.now() - start) + ' milliseconds');
}

// main -> app_genres
db.main.ensureIndex({'response.results.trackId': 1, 'request.country': 1});
recreate(db.main, [
	{
		$unwind: '$response.results'
	}, {
		$group: {
			_id: {
				id: '$response.results.trackId',
				territory: '$request.country'
			},
			genre: {
				$last: '$response.results.primaryGenreName'
			}
		}
	}
], {'_id.id': 1, '_id.territory': 1}, 'app_genres');

// main -> app_names
recreate(db.main, [
	{
		$unwind: {
			path: '$response.results',
			includeArrayIndex: 'index'
		}
	}, {
		$project: {			
			id: '$response.results.trackId',
			artwork: '$response.results.artworkUrl100',
			territory: '$request.country',
			name: '$response.results.trackName',
			ranking: {
				$cond: {
					if: {
						$gt: ['$response.resultCount', 1],
					},
					then: {
						$add: ['$index', 1]
					},
					else: null
				}
			},
			resultCount: '$response.resultCount',
			ts: '$ts',
			userRatingCount: '$response.results.userRatingCount'
		}
	}, {
		$group: {
			_id: {
				id: '$id',
				territory: '$territory'
			},
			name: {
				$last: '$name'
			},
			artwork: {
				$last: '$artwork'
			},
			ranking: {
				$avg: '$ranking'
			},
			userRatingCount: {
				$last: '$userRatingCount'
			},
			ts: {
				$max: '$ts'
			}
		}
	}
], {'_id.id': 1, '_id.territory': 1}, 'app_names');

// main -> numeric_requests
recreate(db.main, [
	{
		$project: {
			id: {
				$convert: {
					input: '$request.term',
					to: 'int',
					onError: null
				}
			},
			territory: '$request.country',
			main_id: '$_id',
			resultCount: '$response.resultCount',
			ts: '$ts'
		}
	}, {
		$match: {
			'id': {
				$ne: null
			}
		}
	}
], {id: 1}, 'numeric_requests');

// main -> searches
recreate(db.main, [
	{
		$match: {
			'ts': {
				$gt: Math.round(new Date().getTime() / 1000) - 60 * 60 * 24 * 7
			}
		}
	},
	{
		$project: {
			_id: {
				$toLower: '$request.term'
			},
			numericTerm: {
				$convert: {
					input: '$request.term',
					to: 'int',
					onError: null
				}
			}
		}
	}, {
		$match: {
			'numericTerm': null
		}
	}, {
		$group: {
			_id: '$_id',
			count: {
				$sum: 1
			}
		}
	}, {
		$sort: {
			count: -1
		}
	}
], {}, 'searches');

// app_names -> app_basics
recreate(db.app_names, [
	{
		$group: {
			_id: {
				id: '$_id.id',
				name: '$name'
			},
			artwork: {
				$first: '$artwork'
			},
			count: {
				$sum: 1
			},
			ranking: {
				$avg: '$ranking'
			},
			ts: {
				$max: '$ts'
			},
			userRatingCount: {
				$avg: '$userRatingCount'
			}
		}
	}, {
		$project: {
			id: '$_id.id',
			name: '$_id.name',
			artwork: '$artwork',
			count: '$count',
			ranking: '$ranking',
			ts: '$ts',
			userRatingCount: '$userRatingCount'
		}
	}, {
		$sort: {
			id: 1,
			count: -1
		}
	}, {
		$group: {
			_id: '$id',
			name: {
				$first: '$name'
			},
			artwork: {
				$first: '$artwork'
			},
			ranking: {
				$avg: '$ranking'
			},
			ts: {
				$max: '$ts'
			},
			userRatingCount: {
				$avg: '$userRatingCount'
			}
		}
	}
], {_id: 1}, 'app_basics');

// app_basics + numeric_requests -> tmp
recreate(db.app_basics, [
	{
		$lookup: {
			from: 'numeric_requests',
			localField: '_id',
			foreignField: 'id',
			as: 'numeric_request'
		}
	}, {
		$unwind: {
			path: '$numeric_request',
			preserveNullAndEmptyArrays: false
		}
	}, {
		$project: {
			_id: false,
			id: '$numeric_request.id',
			territory: '$numeric_request.territory',
			main_id: '$numeric_request.main_id',
			ts : '$numeric_request.ts',
			available: { $cond: {
				if: { $eq: ['$numeric_request.resultCount', 1] },
				then: true,
				else: false
			} }
		}
	}
], {id: 1, territory: 1, ts: 1}, 'tmp');

// main -> tmp (merge)
recreate(db.main, [
	{
		$unwind: '$response.results'
	}, {
		$project: {
			_id: false,
			id: '$response.results.trackId',
			territory: '$request.country',
			main_id: '$_id',
			ts : '$ts',
			available: { $literal: true }
		}
	}
], {id: 1, territory: 1, ts: 1}, 'tmp', true);

// tmp -> statuses
recreate(db.tmp, [
	{
		$sort: {
			id: 1,
			territory: 1,
			ts: 1
		}
	}
], {id: 1, territory: 1, ts: 1}, 'statuses');
db.statuses.ensureIndex({ts: 1});

// statuses -> avg_statuses
recreate(db.statuses, [
	{
		$group: {
			_id: {
				id: '$id',
				territory: '$territory'
			},
			available: {
				$avg: {
					$cond: {
						if: '$available',
						then: 1,
						else: 0
					}
				}
			}
		}
	}, {
		$project: {
			_id: false,
			id: '$_id.id',
			territory: '$_id.territory',
			available: '$available',
			dissonance: {
				$subtract: [.5, {
					$abs: {
						$subtract: ['$available', .5]
					}
				}]
			}
		}
	}
], {}, 'avg_statuses');

// statuses -> avg_status_dissonance
recreate(db.statuses, [
	{
		$match: {
			available: {$gt: 0.0, $lt: 1}
		}
	}, {
		$project: {
			_id: false,
			id: '$id',
			territory: '$territory',
			available: '$available',
			dissonance: {
				$subtract: [.5, {
					$abs: {
						$subtract: ['$available', .5]
					}
				}]
			}
		}
	}
], {}, 'avg_status_dissonance');

// statuses -> agg_statuses
recreate(db.statuses, [
	{
		$group: {
			_id: {
				id: '$id',
				territory: '$territory'
			},
			last_available: {
				$last: '$available'
			},
			min_available: {
				$min: '$available'
			},
			max_available: {
				$max: '$available'
			},
			last_ts: {
				$last: '$ts'
			},
			last_available_ts: {
				$max: {
					$cond: {
						if: { $eq: ['$available', true] },
						then: '$ts',
						else: 0
					}
				}
			},
			last_unavailable_ts: {
				$max: {
					$cond: {
						if: { $eq: ['$available', false] },
						then: '$ts',
						else: 0
					}
				}
			}
		}
	}, {
		$project: {
			id: '$_id.id',
			territory: '$_id.territory',
			last_available: '$last_available',
			min_available: '$min_available',
			max_available: '$max_available',
			last_ts: '$ts',
			last_available_ts: '$last_available_ts',
			last_unavailable_ts: '$last_unavailable_ts'
		}
	}
], {}, 'agg_statuses');

// agg_statuses + app_basics -> apps
recreate(db.agg_statuses, [
	{
		$group: {
			_id: '$id',
			territories: {
				$sum: 1
			},
			available: {
				$sum: {
					$cond: {
						if: { $eq: ['$last_available', true] },
						then: 1,
						else: 0
					}
				}
			},
			last_available_ts: {
				$max: '$last_available_ts'
			},
			last_unavailable_ts: {
				$max: '$last_unavailable_ts'
			}
		}
	}, {
		$lookup: {
			from: 'app_basics',
			localField: '_id',
			foreignField: '_id',
			as: 'app'
		}
	}, {
		$unwind: {
			path: '$app'
		}
	}, {
		$project: {
			_id: '$_id',
			name: '$app.name',
			artwork: '$app.artwork',
			ranking: '$app.ranking',
			ts: '$app.ts',
			userRatingCount: '$app.userRatingCount',
			territories: '$territories',
			available: '$available',
			last_available_ts: '$last_available_ts',
			last_unavailable_ts: '$last_unavailable_ts'
		}
	}
], {}, 'apps');

// apps -> available_app_ids
recreate(db.apps, [
	{
		$match: {
			available: {
 				$gt: 0
			}
		}
	}, {
		$project: {
			_id: '$_id'
		}
	}
], {}, 'available_app_ids');

// agg_statuses -> territories
recreate(db.agg_statuses, [
	{
		$lookup: {
			from: 'available_app_ids',
			localField: 'id',
			foreignField: '_id',
			as: 'available_app_id'
		}
	}, {
		$match: {
			available_app_id: {
				$ne: []
			}
		}
	},
	{
		$group: {
			_id: '$territory',
			apps_count: {
				$sum: 1
			},
			apps_unavailable: {
				$sum: {
					$cond: {
						if: { $eq: ['$last_available', true] },
						then: 0,
						else: 1
					}
				}
			}
		}
	}, {
		$match: {
			_id: {
				$ne: null
			}
		}
	}
], {}, 'territories');

// agg_statuses + app_genres -> territory_genres
recreate(db.agg_statuses, [
	{
		$lookup: {
			from: 'available_app_ids',
			localField: 'id',
			foreignField: '_id',
			as: 'available_app_id'
		}
	}, {
		$match: {
			available_app_id: {
				$ne: []
			}
		}
	},
	{
		$lookup: {
			from: 'app_genres',
			localField: '_id.id',
			foreignField: '_id.id',
			as: 'app_genre'
		}
	}, {
		$unwind: {
			path: '$app_genre',
			preserveNullAndEmptyArrays: false
		}
	}, {
		$match: {
			'app_genre._id.territory': {
				$eq: 'US'
			}
		}
	}, {
		$group: {
			_id: {
				territory: '$territory',
				genre: '$app_genre.genre'
			},
			apps_count: {
				$sum: 1
			},
			apps_unavailable: {
				$sum: {
					$cond: {
						if: { $eq: ['$last_available', true] },
						then: 0,
						else: 1
					}
				}
			}
		}
	}, {
		$match: {
			'_id.territory': {
				$ne: null
			}
		}
	}
], {}, 'territory_genres');

print('mongo.js done');

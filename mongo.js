db.main.aggregate([
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
	}, {
		$out: 'tmp'
	}
]);
db.tmp.ensureIndex({'_id.id': 1, '_id.territory': 1});
db.tmp.renameCollection('app_names', true);
print('recreated app_names');

db.app_names.aggregate([
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
	}, {
		$out: 'tmp'
	}
]);
db.tmp.renameCollection('apps', true);
print('recreated apps');

db.main.aggregate([
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
	}, {
		$out: 'tmp'
	}
]);
db.tmp.ensureIndex({'_id.id': 1, '_id.territory': 1});
db.tmp.renameCollection('app_genres', true);
print('recreated app_genres');

db.main.aggregate([
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
	}, {
		$out: 'tmp'
	}
]);
db.tmp.renameCollection('searches', true);
print('recreated searches');

db.main.aggregate([
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
	}, {
		$out: 'tmp'
	}
]);
db.tmp.renameCollection('numeric_requests', true);
print('recreated numeric_requests');

db.apps.aggregate([
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
	}, {
		$out: 'tmp'
	}
]);
db.tmp.renameCollection('statuses1', true);
print('recreated statuses1');

db.main.aggregate([
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
	}, {
		$out: 'tmp'
	}
]);
db.tmp.renameCollection('statuses2', true);
print('recreated statuses2');

db.statuses1.copyTo('tmp');
db.statuses2.copyTo('tmp');
db.tmp.renameCollection('statuses', true);
print('recreated statuses');

db.statuses.aggregate([
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
	}, {
		$out: 'tmp'
	}
]);
db.tmp.renameCollection('avg_statuses', true);
print('recreated avg_statuses');

db.avg_statuses.aggregate([
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
	}, {
		$out: 'tmp'
	}
]);
db.tmp.renameCollection('avg_status_dissonance', true);
print('recreated avg_status_dissonance');

db.statuses.aggregate([
	{
		$group: {
			_id: {
				id: '$id',
				territory: '$territory'
			},
			available: {
				$last: '$available'
			},
			ts: {
				$max: '$ts'
			}
		}
	}, {
		$project: {
			id: '$_id.id',
			territory: '$_id.territory',
			available: '$available',
			ts: '$ts'
		}
	}, {
		$out: 'tmp'
	}
]);
db.tmp.renameCollection('last_statuses', true);
print('recreated last_statuses');

db.last_statuses.aggregate([
	{
		$group: {
			_id: '$territory',
			apps_count: {
				$sum: 1
			},
			apps_unavailable: {
				$sum: {
					$cond: {
						if: { $eq: ['$available', true] },
						then: 0,
						else: 1
					}
				}
			}
		}
	}, {
		$match: {
			'_id': {
				$ne: null
			}
		}
	}, {
		$out: 'tmp'
	}
]);
db.tmp.renameCollection('territories', true);
print('recreated territories');

db.last_statuses.aggregate([
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
						if: { $eq: ['$available', true] },
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
	}, {
		$out: 'tmp'
	}
]);
db.tmp.renameCollection('territory_genres', true);
print('recreated territory_genres');

print('mongo.js done');

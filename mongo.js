db.main.aggregate([
	{
		$unwind: {
			path: '$response.results',
			includeArrayIndex: 'index'
		}
	}, {
		$project: {			
			id: '$response.results.trackId',
			country: '$request.country',
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
		}
	}, {
		$group: {
			_id: {
				id: '$id',
				country: '$country'
			},
			name: {
				$last: '$name'
			},
			ranking: {
				$avg: '$ranking'
			}
		}
	}, {
		$out: 'tmp'
	}
]);
db.tmp.ensureIndex({'_id.id': 1, '_id.country': 1});
db.tmp.renameCollection('app_names', true);
print('recreated app_names');

db.app_names.aggregate([
	{
		$group: {
			_id: {
				id: '$_id.id',
				name: '$name'
			},
			count: {
				$sum: 1
			},
			ranking: {
				$avg: '$ranking'
			}
		}
	}, {
		$project: {
			id: '$_id.id',
			name: '$_id.name',
			count: '$count',
			ranking: '$ranking'
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
			ranking: {
				$avg: '$ranking'
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
				country: '$request.country'
			},
			genre: {
				$last: '$response.results.primaryGenreName'
			}
		}
	}, {
		$out: 'tmp'
	}
]);
db.tmp.ensureIndex({'_id.id': 1, '_id.country': 1});
db.tmp.renameCollection('app_genres', true);
print('recreated app_genres');

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
			country: '$request.country',
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
			country: '$numeric_request.country',
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
			country: '$request.country',
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
				country: '$country'
			},
			available: {
				$last: '$available'
			}
		}
	}, {
		$project: {
			id: '$_id.id',
			country: '$_id.country',
			available: '$available'
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
			_id: '$country',
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
		$out: 'tmp'
	}
]);
db.tmp.renameCollection('countries', true);
print('recreated countries');

<?php

class lastfmApiArtist extends lastfmApiBase {
	public $events;
	public $info;
	public $similar;
	public $tags;
	public $topAlbums;
	public $topFans;
	public $topTags;
	public $topTracks;
	
	private $apiKey;
	private $artist;
	private $mbid;
	private $auth;
	
	function __construct($apiKey, $artist = '', $mbid = '') {
		$this->apiKey = $apiKey;
		$this->artist = $artist;
		$this->mbid = $mbid;
	}
	
	public function getEvents() {
		$vars = array(
			'method' => 'artist.getevents',
			'api_key' => $this->apiKey,
			'artist' => $this->artist
		);
		
		$call = $this->apiGetCall($vars);
		
		if ( $call['status'] == 'ok' ) {
			if ( $call->events['total'] != 0 ) {
				$i = 0;
				$this->events = '';
				foreach ( $call->events->event as $event ) {
					$this->events[$i]['id'] = (string) $event->id;
					$this->events[$i]['title'] = (string) $event->title;
					$ii = 0;
					foreach ( $event->artists->artist as $artist ) {
						$this->events[$i]['artists'][$ii] = (string) $artist;
						$ii++;
					}
					$this->events[$i]['headliner'] = (string) $event->artists->headliner;
					$this->events[$i]['venue']['name'] = (string) $event->venue->name;
					$this->events[$i]['venue']['location']['city'] = (string) $event->venue->location->city;
					$this->events[$i]['venue']['location']['country'] = (string) $event->venue->location->country;
					$this->events[$i]['venue']['location']['street'] = (string) $event->venue->location->street;
					$this->events[$i]['venue']['location']['postcode'] = (string) $event->venue->location->postalcode;
					$geopoint =  $event->venue->location->children('http://www.w3.org/2003/01/geo/wgs84_pos#');
					$this->events[$i]['venue']['location']['geopoint']['lat'] = (string) $geopoint->point->lat;
					$this->events[$i]['venue']['location']['geopoint']['long'] = (string) $geopoint->point->long;
					$this->events[$i]['venue']['location']['timezone'] = (string) $event->venue->location->timezone;
					$this->events[$i]['venue']['url'] = (string) $call->venue->url;
					$this->events[$i]['startdate'] = strtotime(trim((string) $event->startDate));
					$this->events[$i]['description'] = (string) $event->description;
					$this->events[$i]['image']['small'] = (string) $event->image[0];
					$this->events[$i]['image']['mendium'] = (string) $event->image[1];
					$this->events[$i]['image']['large'] = (string) $event->image[2];
					$this->events[$i]['attendance'] = (string) $event->attendance;
					$this->events[$i]['reviews'] = (string) $event->reviews;
					$this->events[$i]['tag'] = (string) $event->tag;
					$this->events[$i]['url'] = (string) $event->url;
					
					$i++;
				}
				
				return $this->events;
			}
			else {
				// No events are found
				$this->error['code'] = 90;
				$this->error['desc'] = 'Artist has no events';
				return FALSE;
			}
		}
		elseif ( $call['status'] == 'failed' ) {
			// Fail with error code
			$this->error['code'] = $call->error['code'];
			$this->error['desc'] = $call->error;
			return FALSE;
		}
		else {
			//Hard failure
			$this->error['code'] = 0;
			$this->error['desc'] = 'Unknown error';
			return FALSE;
		}
	}
	
	public function getInfo() {
		$vars = array(
			'method' => 'artist.getinfo',
			'api_key' => $this->apiKey,
			'mbid' => $this->mbid,
			'artist' => $this->artist
		);
		
		$call = $this->apiGetCall($vars);
		
		if ( $call['status'] == 'ok' ) {
			$this->info = '';
			$this->info['name'] = (string) $call->artist[0]->name;
			$this->info['mbid'] = (string) $call->artist[0]->mbid;
			$this->info['url'] = (string) $call->artist[0]->url;
			$this->info['image']['small'] = (string) $call->artist[0]->image[0];
			$this->info['image']['medium'] = (string) $call->artist[0]->image[1];
			$this->info['image']['large'] = (string) $call->artist[0]->image[2];
			$this->info['streamable'] = (string) $call->artist[0]->streamable;
			$this->info['stats']['listeners'] = (string) $call->artist[0]->stats->listeners;
			$this->info['stats']['playcount'] = (string) $call->artist[0]->stats->plays;
			$i = 0;
			foreach ( $call->artist[0]->similar->artist as $artist ) {
				$this->info['similar'][$i]['name'] = (string) $artist->name;
				$this->info['similar'][$i]['url'] = (string) $artist->url;
				$this->info['similar'][$i]['image']['small'] = (string) $artist->image[0];
				$this->info['similar'][$i]['image']['medium'] = (string) $artist->image[1];
				$this->info['similar'][$i]['image']['large'] = (string) $artist->image[2];
				$i++;
			}
			$i = 0;
			foreach ( $call->artist[0]->tags->tag as $tag ) {
				$this->tags[$i]['name'] = (string) $tag->name;
				$this->tags[$i]['url'] = (string) $tag->url;
				$i++;
			}
			$this->info['tags'] = $this->tags;
			$this->info['bio']['published'] = (string) $call->artist[0]->bio->published;
			$this->info['bio']['summary'] = (string) $call->artist[0]->bio->summary;
			$this->info['bio']['content'] = (string) $call->artist[0]->bio->content;
			
			return $this->info;
		}
		elseif ( $call['status'] == 'failed' ) {
			// Fail with error code
			$this->error['code'] = $call->error['code'];
			$this->error['desc'] = $call->error;
			return FALSE;
		}
		else {
			//Hard failure
			$this->error['code'] = 0;
			$this->error['desc'] = 'Unknown error';
			return FALSE;
		}
	}
	
	public function getSimilar($limit = '') {
		$vars = array(
			'method' => 'artist.getsimilar',
			'api_key' => $this->apiKey,
			'artist' => $this->artist
		);
		if ( !empty($limit) ) {
			$vars['limit'] = $limit;
		}
		
		$call = $this->apiGetCall($vars);
		
		if ( $call['status'] == 'ok' ) {
			$this->similar = '';
			$i = 0;
			foreach ( $call->similarartists->artist as $artist ) {
				$this->similar[$i]['name'] = (string) $artist->name;
				$this->similar[$i]['mbid'] = (string) $artist->mbid;
				$this->similar[$i]['match'] = (string) $artist->match;
				$this->similar[$i]['url'] = (string) $artist->url;
				$this->similar[$i]['image'] = (string) $artist->image;
				$i++;
			}
			
			return $this->similar;
		}
		elseif ( $call['status'] == 'failed' ) {
			// Fail with error code
			$this->error['code'] = $call->error['code'];
			$this->error['desc'] = $call->error;
			return FALSE;
		}
		else {
			//Hard failure
			$this->error['code'] = 0;
			$this->error['desc'] = 'Unknown error';
			return FALSE;
		}
	}
	
	public function getTags($sessionKey, $secret) {
		$vars = array(
			'method' => 'artist.gettags',
			'api_key' => $this->apiKey,
			'sk' => $sessionKey,
			'artist' => $this->artist
		);
		$sig = $this->apiSig($secret, $vars);
		$vars['api_sig'] = $sig;
		
		$call = $this->apiGetCall($vars);
		
		if ( $call['status'] == 'ok' ) {
			if ( count($call->tags->tag) > 0 ) {
				$i = 0;
				foreach ( $call->tags[0]->tag as $tag ) {
					$this->tags[$i]['name'] = (string) $tag->name;
					$this->tags[$i]['url'] = (string) $tag->url;
					$i++;
				}
				
				return $this->tags;
			}
			else {
				// No tagsare found
				$this->error['code'] = 90;
				$this->error['desc'] = 'Artist has no tags from this user';
				return FALSE;
			}
		}
		elseif ( $call['status'] == 'failed' ) {
			// Fail with error code
			$this->error['code'] = $call->error['code'];
			$this->error['desc'] = $call->error;
			return FALSE;
		}
		else {
			//Hard failure
			$this->error['code'] = 0;
			$this->error['desc'] = 'Unknown error';
			return FALSE;
		}
	}
	
	public function getTopAlbums() {
		$vars = array(
			'method' => 'artist.gettopalbums',
			'api_key' => $this->apiKey,
			'artist' => $this->artist
		);
		
		$call = $this->apiGetCall($vars);
		
		if ( $call['status'] == 'ok' ) {
			if ( count($call->topalbums->album) > 0 ) {
				$i = 0;
				foreach ( $call->topalbums->album as $album ) {
					$this->topAlbums[$i]['rank'] = (string) $album['rank'];
					$this->topAlbums[$i]['name'] = (string) $album->name;
					$this->topAlbums[$i]['mbid'] = (string) $album->mbid;
					$this->topAlbums[$i]['playcount'] = (string) $album->playcount;
					$this->topAlbums[$i]['url'] = (string) $album->url;
					$this->topAlbums[$i]['image']['small'] = (string) $album->image[0];
					$this->topAlbums[$i]['image']['medium'] = (string) $album->image[1];
					$this->topAlbums[$i]['image']['large'] = (string) $album->image[2];
					$i++;
				}
				
				return $this->topAlbums;
			}
			else {
				// No tagsare found
				$this->error['code'] = 90;
				$this->error['desc'] = 'Artist has no top albums';
				return FALSE;
			}
		}
		elseif ( $call['status'] == 'failed' ) {
			// Fail with error code
			$this->error['code'] = $call->error['code'];
			$this->error['desc'] = $call->error;
			return FALSE;
		}
		else {
			//Hard failure
			$this->error['code'] = 0;
			$this->error['desc'] = 'Unknown error';
			return FALSE;
		}
	}
	
	public function getTopFans() {
		$vars = array(
			'method' => 'artist.gettopfans',
			'api_key' => $this->apiKey,
			'artist' => $this->artist
		);
		
		$call = $this->apiGetCall($vars);
		
		if ( $call['status'] == 'ok' ) {
			if ( count($call->topfans->user) > 0 ) {
				$i = 0;
				foreach ( $call->topfans->user as $user ) {
					$this->topFans[$i]['name'] = (string) $user->name;
					$this->topFans[$i]['url'] = (string) $user->url;
					$this->topFans[$i]['image']['small'] = (string) $user->image[0];
					$this->topFans[$i]['image']['medium'] = (string) $user->image[1];
					$this->topFans[$i]['image']['large'] = (string) $user->image[2];
					$this->topFans[$i]['weight'] = (string) $user->weight;
					$i++;
				}
				
				return $this->topFans;
			}
			else {
				// No tagsare found
				$this->error['code'] = 90;
				$this->error['desc'] = 'Artist has no top users';
				return FALSE;
			}
		}
		elseif ( $call['status'] == 'failed' ) {
			// Fail with error code
			$this->error['code'] = $call->error['code'];
			$this->error['desc'] = $call->error;
			return FALSE;
		}
		else {
			//Hard failure
			$this->error['code'] = 0;
			$this->error['desc'] = 'Unknown error';
			return FALSE;
		}
	}
	
	public function getTopTags() {
		$vars = array(
			'method' => 'artist.gettoptags',
			'api_key' => $this->apiKey,
			'artist' => $this->artist
		);
		
		$call = $this->apiGetCall($vars);
		
		if ( $call['status'] == 'ok' ) {
			if ( count($call->toptags->tag) > 0 ) {
				$i = 0;
				foreach ( $call->toptags->tag as $tag ) {
					$this->topTags[$i]['name'] = (string) $tag->name;
					$this->topTags[$i]['url'] = (string) $tag->url;
					$i++;
				}
				
				return $this->topTags;
			}
			else {
				// No tagsare found
				$this->error['code'] = 90;
				$this->error['desc'] = 'Artist has no top tags';
				return FALSE;
			}
		}
		elseif ( $call['status'] == 'failed' ) {
			// Fail with error code
			$this->error['code'] = $call->error['code'];
			$this->error['desc'] = $call->error;
			return FALSE;
		}
		else {
			//Hard failure
			$this->error['code'] = 0;
			$this->error['desc'] = 'Unknown error';
			return FALSE;
		}
	}
	
	public function getTopTracks() {
		$vars = array(
			'method' => 'artist.gettoptracks',
			'api_key' => $this->apiKey,
			'artist' => $this->artist
		);
		
		$call = $this->apiGetCall($vars);
		
		if ( $call['status'] == 'ok' ) {
			if ( count($call->toptracks->track) > 0 ) {
				$i = 0;
				foreach ( $call->toptracks->track as $tracks ) {
					$this->topTracks[$i]['rank'] = (string) $tracks['rank'];
					$this->topTracks[$i]['name'] = (string) $tracks->name;
					$this->topTracks[$i]['playcount'] = (string) $tracks->playcount;
					$this->topTracks[$i]['streamable'] = (string) $tracks->streamable;
					$this->topTracks[$i]['url'] = (string) $tracks->url;
					$this->topTracks[$i]['image']['small'] = (string) $tracks->image[0];
					$this->topTracks[$i]['image']['medium'] = (string) $tracks->image[1];
					$this->topTracks[$i]['image']['large'] = (string) $tracks->image[2];
					$i++;
				}
				
				return $this->topTracks;
			}
			else {
				// No tagsare found
				$this->error['code'] = 90;
				$this->error['desc'] = 'Artist has no top tracks';
				return FALSE;
			}
		}
		elseif ( $call['status'] == 'failed' ) {
			// Fail with error code
			$this->error['code'] = $call->error['code'];
			$this->error['desc'] = $call->error;
			return FALSE;
		}
		else {
			//Hard failure
			$this->error['code'] = 0;
			$this->error['desc'] = 'Unknown error';
			return FALSE;
		}
	}
	
	public function search($page = '', $limit = '') {
		$vars = array(
			'method' => 'artist.search',
			'api_key' => $this->apiKey,
			'artist' => $this->artist
		);
		if ( !empty($limit) ) {
			$vars['limit'] = $limit;
		}
		if ( !empty($page) ) {
			$vars['page'] = $page;
		}
		
		$call = $this->apiGetCall($vars);
		
		if ( $call['status'] == 'ok' ) {
			$opensearch = $call->results->children('http://a9.com/-/spec/opensearch/1.1/');
			if ( $opensearch->totalResults > 0 ) {
				$this->searchResults['totalResults'] = (string) $opensearch->totalResults;
				$this->searchResults['startIndex'] = (string) $opensearch->startIndex;
				$this->searchResults['itemsPerPage'] = (string) $opensearch->itemsPerPage;
				$i = 0;
				foreach ( $call->results->artistmatches->artist as $artist ) {
					$this->searchResults['results'][$i]['name'] = (string) $artist->name;
					$this->searchResults['results'][$i]['mbid'] = (string) $artist->mbid;
					$this->searchResults['results'][$i]['url'] = (string) $artist->url;
					$this->searchResults['results'][$i]['streamable'] = (string) $artist->streamable;
					$this->searchResults['results'][$i]['image']['small'] = (string) $artist->image_small;
					$this->searchResults['results'][$i]['image']['large'] = (string) $artist->image;
					$i++;
				}
				
				return $this->searchResults;
			}
			else {
				// No tagsare found
				$this->error['code'] = 90;
				$this->error['desc'] = 'No results';
				return FALSE;
			}
		}
		elseif ( $call['status'] == 'failed' ) {
			// Fail with error code
			$this->error['code'] = $call->error['code'];
			$this->error['desc'] = $call->error;
			return FALSE;
		}
		else {
			//Hard failure
			$this->error['code'] = 0;
			$this->error['desc'] = 'Unknown error';
			return FALSE;
		}
	}
}

?>
<?php

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER["SCRIPT_NAME"],basename(__FILE__)) !== false) {
  header("location: index.php");
  exit;
}

class RankLib extends TikiLib {
	function wiki_ranking_top_pages($limit, $categ=array(), $lang=null) {
		global $user, $prefs;
		
		$bindvals = array();
		$mid = '';
		if ($categ) {
			$mid .= " INNER JOIN (`tiki_objects` as tob, `tiki_category_objects` as tco) ON (tp.`pageName` = tob.`itemId` and tob.`objectId` = tco.`catObjectId`) WHERE tob.`type` = 'wiki page' AND (tco.`categId` = ?";
			$bindvals[] = $categ[0]; 	
			for ($i = 1; $i < count($categ); $i++) {
				$mid .= " OR tco.`categId` = " . $categ[$i];
			}
			$mid .= ")";
		}
		
		if ($prefs['feature_wikiapproval'] == 'y') {
			if ($mid) {
				$mid .= " AND tp.`pageName` not like ?";
			} else {
				$mid .= " WHERE tp.`pageName` not like ?";	
			}
			$bindvals[] = $prefs['wikiapproval_prefix'] . '%';
		}
		
		$query = "select distinct tp.`pageName`, tp.`hits`, tp.`lang`, tp.`page_id` from `tiki_pages` tp $mid order by `hits` desc";

		$result = $this->query($query, $bindvals);
		$ret = array();
		$count = 0;
		while (($res = $result->fetchRow()) && $count < $limit) {
			$perms = Perms::get( array( 'type' => 'wiki page', 'object' => $res['pageName'] ) );
			if ( $perms->view ) {
				global $disableBestLang;
				$disableBestLang = false;
				if ($res['lang'] > '' && $prefs['feature_best_language'] == 'y') {
					// find best language equivalent
					if (isset($_REQUEST['bl']) || isset($_REQUEST['best_lang'])) {
						global $multilinguallib;
						include_once('lib/multilingual/multilinguallib.php');
						$bestLangPageId = $multilinguallib->selectLangObj('wiki page', $res['page_id'], null, 'tiki_p_view');
						if ($res['page_id'] != $bestLangPageId) {							
							$res['pageName'] = $this->get_page_name_from_id($bestLangPageId);
						}
					}
				}		
				if ($prefs['feature_best_language'] != 'y' || !$res['lang'] || !in_array($res['pageName'],$pagesAdded)) {
					$aux['name'] = $res['pageName'];
					$aux['hits'] = $res['hits'];
					$aux['href'] = 'tiki-index.php?page=' . urlencode($res['pageName']);
					if ($disableBestLang == true) $aux['href'] .= '&amp;bl=n'; 	
					$ret[] = $aux;
					$pagesAdded[] = $res['pageName'];
					++$count;
				}				
			}
		}

		$retval["data"] = $ret;
		$retval["title"] = tra("Wiki top pages");
		$retval["y"] = tra("Hits");
		$retval["type"] = "nb";
		return $retval;
	}

	function wiki_ranking_top_pagerank($limit, $categ=array()) {
		global $user, $prefs;

		$roll = rand( 1, (int) $prefs['wiki_ranking_reload_probability'] );
		if( $roll == 1 ) {
			$this->pageRank();
		}

		$bindvals = array();
		$mid = '';
		if ($categ) {
			$mid .= " INNER JOIN (`tiki_objects` as tob, `tiki_category_objects` as tco) ON (tp.`pageName` = tob.`itemId` and tob.`objectId` = tco.`catObjectId`) WHERE tob.`type` = 'wiki page' AND (tco.`categId` = ?";
			$bindvals[] = $categ[0]; 	
			for ($i = 1; $i < count($categ); $i++) {
				$mid .= " OR tco.`categId` = " . $categ[$i];
			}
			$mid .= ")";
		}
		if ($prefs['feature_wikiapproval'] == 'y') {
			if ($mid) {
				$mid .= " AND tp.`pageName` not like ?";
			} else {
				$mid .= " WHERE tp.`pageName` not like ?";	
			}
			$bindvals[] = $prefs['wikiapproval_prefix'] . '%';
		}
		
		$query = "select tp.`pageName`, tp.`pageRank` from `tiki_pages` tp $mid order by `pageRank` desc";
		
		$result = $this->query($query, $bindvals);
		$ret = array();
		$count = 0;
		while (($res = $result->fetchRow()) && $count < $limit) {
			if ($this->user_has_perm_on_object($user,$res['pageName'], 'wiki page', 'tiki_p_view')) {
				$aux['name'] = $res['pageName'];
				$aux['hits'] = $res['pageRank'];
				$aux['href'] = 'tiki-index.php?page=' . urlencode($res['pageName']);
				$ret[] = $aux;
				++$count;
			}
		}

		$retval["data"] = $ret;
		$retval["title"] = tra("Most relevant pages");
		$retval["y"] = tra("Relevance");
		$retval["type"] = "nb";
		return $retval;
	}

	function wiki_ranking_last_pages($limit, $categ=array()) {
		global $user, $prefs;
		
		$bindvals = array();
		$mid = '';
		if ($categ) {
			$mid .= " INNER JOIN (`tiki_objects` as tob, `tiki_category_objects` as tco) ON (tp.`pageName` = tob.`itemId` and tob.`objectId` = tco.`catObjectId`) WHERE tob.`type` = 'wiki page' AND (tco.`categId` = ?";
			$bindvals[] = $categ[0]; 	
			for ($i = 1; $i < count($categ); $i++) {
				$mid .= " OR tco.`categId` = " . $categ[$i];
			}
			$mid .= ")";
		}
		if ($prefs['feature_wikiapproval'] == 'y') {
			if ($mid) {
				$mid .= " AND tp.`pageName` not like ?";
			} else {
				$mid .= " WHERE tp.`pageName` not like ?";	
			}
			$bindvals[] = $prefs['wikiapproval_prefix'] . '%';
		}
		
		$query = "select tp.`pageName`,tp.`lastModif`,tp.`hits` from `tiki_pages` tp $mid order by `lastModif` desc";

		$result = $this->query($query, $bindvals);
		$ret = array();
		$count = 0;
		while (($res = $result->fetchRow()) && $count < $limit) {
			if ($this->user_has_perm_on_object($user,$res['pageName'], 'wiki page', 'tiki_p_view')) {
				$aux['name'] = $res['pageName'];
				$aux['hits'] = $res['lastModif'];
				$aux['href'] = 'tiki-index.php?page=' . urlencode($res['pageName']);
				$ret[] = $aux;
				++$count;
			}
		}

		$retval["data"] = $ret;
		$retval["title"] = tra("Wiki last pages");
		$retval["y"] = tra("Modified");
		$retval["type"] = "date";
		return $retval;
	}

	function forums_ranking_last_replied_topics($limit, $forumId='', $last_replied=true) {
		$retval = $this->forums_ranking_last_topics($limit, $forumId, $last_replied);
		return $retval;
	}
	
		$query = "select a.*, tf.*, max(b.`commentDate`) as `lastPost` from
		`tiki_comments` a left join `tiki_comments` b on b.`parentId`=a.`threadId` right join `tiki_forums` tf on "
		.$this->cast("tf.`forumId`","string")." = a.`object`".
		" where	a.`objectType` = 'forum' and a.`parentId`=0 $mid group by a.`threadId` order by `lastPost` desc";
		}
		$offset=0;
		$count = 0;
		$ret = array();
					$aux['name'] = $res['name'] . ': ' . $res['title'];
					$aux['title'] = $res['title'];
					$aux['hits'] = $this->get_long_datetime($res['commentDate']);
					$tmp = $res['parentId'];
					if ($tmp == 0) $tmp = $res['threadId'];
					$aux['href'] = 'tiki-view_forum_thread.php?forumId=' . $res['forumId'] . '&amp;comments_parentId=' . $tmp;
					$aux['date'] = $res['commentDate'];
					$aux['user'] = $res['userName'];
					$ret[] = $aux;
					++$count;
				}
			} else {
				break;
			}
		}
		$retval["data"] = $ret;
		$retval["title"] = tra("Forums last posts");
		$retval["y"] = tra("Topic date");
		$retval["type"] = "date";
		return $retval;
	}

			$bindvars = array();
			$mid = '';
		}
		$query = "select
		tc.`hits`,tc.`title`,tf.`name`,tf.`forumId`,tc.`threadId`,tc.`object`
		from `tiki_comments` tc,`tiki_forums` tf where
		`object`=`forumId` and `objectType` = 'forum' and
		`parentId`=0 $mid order by tc.`hits` desc";

		$ret = array();
                 if ($this->user_has_perm_on_object($user, $res['forumId'], 'forum', 'tiki_p_forum_read')) {
				$aux['name'] = $res['name'] . ': ' . $res['title'];
				$aux['title'] = $res['title'];
				$aux['hits'] = $res['hits'];
				$aux['href'] = 'tiki-view_forum_thread.php?forumId=' . $res['forumId'] . '&amp;comments_parentId=' . $res['threadId'];
				$ret[] = $aux;
			}

		$retval["data"] = $ret;
		$retval["title"] = tra("Forums most read topics");
		$retval["y"] = tra("Reads");
		$retval["type"] = "nb";
		return $retval;
	}

    function forums_top_posters($qty) {
        $query = "select `user`, `posts` from `tiki_user_postings` order by ".$this->convertSortMode("posts_desc");
        $result = $this->query($query, array(),$qty);
        $ret = array();

        while ($res = $result->fetchRow()) {
            $aux["name"] = $res["user"];
	    $aux["posts"] = $res["posts"];
	    $ret[] = $aux;
        }

	$retval["data"] = $ret;

        return $retval;
    }


	function forums_ranking_top_topics($limit) {
		$query = "select
		tc.`average`,tc.`title`,tf.`name`,tf.`forumId`,tc.`threadId`,tc.`object`
		from `tiki_comments` tc,`tiki_forums` tf where
		`object`=`forumId` and `objectType` = 'forum' and
		`parentId`=0 order by tc.`average` desc";

		$ret = array();
			if ($this->user_has_perm_on_object($user, $res['forumId'], 'forum', 'tiki_p_forum_read')) {
				$aux['name'] = $res['name'] . ': ' . $res['title'];
				$aux['title'] = $res['title'];
				$aux['hits'] = $res['average'];
				$aux['href'] = 'tiki-view_forum_thread.php?forumId=' . $res['forumId'] . '&amp;comments_parentId=' . $res['threadId'];
				$ret[] = $aux;
				++$count;
			}
		}

		$retval["data"] = $ret;
		$retval["title"] = tra("Forums best topics");
		$retval["y"] = tra("Score");
		$retval["type"] = "nb";
		return $retval;
	}

	function forums_ranking_most_visited_forums($limit) {
		global $commentslib;
		if( ! $commentslib ) {
			require_once 'lib/commentslib.php';
		global $user;
		$query = "select * from `tiki_forums` order by `hits` desc";

		$result = $this->query($query,array());
		$ret = array();
		$count = 0;
				$aux['hits'] = $res['hits'];
				$aux['href'] = 'tiki-view_forum.php?forumId=' . $res['forumId'];
				$ret[] = $aux;
				++$count;
			}
		}

		$retval["data"] = $ret;
		$retval["title"] = tra("Forums most visited forums");
		$retval["y"] = tra("Visits");
		$retval["type"] = "nb";
		return $retval;
	}

	function forums_ranking_most_commented_forum($limit) {
		global $commentslib;
		if( ! $commentslib ) {
			require_once 'lib/commentslib.php';
		global $user;
		$query = "select * from `tiki_forums` order by `comments` desc";

		$result = $this->query($query,array());
		$ret = array();
		$count = 0;
				$aux['hits'] = $res['comments'];
				$aux['href'] = 'tiki-view_forum.php?forumId=' . $res['forumId'];
				$ret[] = $aux;
				++$count;
			}
		}

		$retval["data"] = $ret;
		$retval["title"] = tra("Forums with most posts");
		$retval["y"] = tra("Posts");
		$retval["type"] = "nb";
		return $retval;
	}

	function gal_ranking_top_galleries($limit) {
		global $user;
		$query = "select * from `tiki_galleries` where `visible`=? order by `hits` desc";

		$result = $this->query($query,array('y'));
		$ret = array();
		$count = 0;
		while (($res = $result->fetchRow()) && $count < $limit) {
			if ($this->user_has_perm_on_object($user, $res['galleryId'], 'image gallery', 'tiki_p_view_image_gallery')) {
				$aux['name'] = $res['name'];
				$aux['hits'] = $res['hits'];
				$aux['href'] = 'tiki-browse_gallery.php?galleryId=' . $res['galleryId'];
				$ret[] = $aux;
				++$count;
			}
		}

		$retval["data"] = $ret;
		$retval["title"] = tra("Wiki top galleries");
		$retval["y"] = tra("Visits");
		$retval["type"] = "nb";
		return $retval;
	}

	function filegal_ranking_top_galleries($limit) {
		global $user;
		$query = "select * from `tiki_file_galleries` where `visible`=? order by `hits` desc";

		$result = $this->query($query,array('y'),$limit,0);
		$ret = array();
		$count = 0;
		while (($res = $result->fetchRow()) && $count < $limit) {
			if ($this->user_has_perm_on_object($user, $res['galleryId'], 'file gallery', 'tiki_p_view_file_gallery')) {
				$aux['name'] = $res['name'];
				$aux['hits'] = $res['hits'];
				$aux['href'] = 'tiki-list_file_gallery.php?galleryId=' . $res['galleryId'];
				$ret[] = $aux;
				++$count;
			}
		}

		$retval["data"] = $ret;
		$retval["title"] = tra("Wiki top file galleries");
		$retval["y"] = tra("Visits");
		$retval["type"] = "nb";
		return $retval;
	}

	function gal_ranking_top_images($limit) {
		global $user;
		$query = "select `imageId`,`name`,`hits`, `galleryId` from `tiki_images` order by `hits` desc";

		$result = $this->query($query,array(),$limit,0);
		$ret = array();

		while ($res = $result->fetchRow()) {
			if ($this->user_has_perm_on_object($user, $res['galleryId'], 'image gallery', 'tiki_p_view_image_gallery')) {
				$aux["name"] = $res["name"];
				$aux["hits"] = $res["hits"];
				$aux["href"] = 'tiki-browse_image.php?imageId=' . $res["imageId"];
				$ret[] = $aux;
			}
		}

		$retval["data"] = $ret;
		$retval["title"] = tra("Wiki top images");
		$retval["y"] = tra("Hits");
		$retval["type"] = "nb";
		return $retval;
	}

	function filegal_ranking_top_files($limit) {
		global $user;
		$query = "select `fileId`,`filename`,`hits`, `galleryId` from `tiki_files` order by `hits` desc";

		$result = $this->query($query,array(),$limit,0);
		$ret = array();

		while ($res = $result->fetchRow()) {
			if ($this->user_has_perm_on_object($user, $res['galleryId'], 'file gallery', 'tiki_p_view_file_gallery')) {
				$aux["name"] = $res["filename"];
				$aux["hits"] = $res["hits"];
				$aux["href"] = 'tiki-download_file.php?fileId=' . $res["fileId"];
				$ret[] = $aux;
			}
		}

		$retval["data"] = $ret;
		$retval["title"] = tra("Wiki top files");
		$retval["y"] = tra("Downloads");
		$retval["type"] = "nb";
		return $retval;
	}

	function gal_ranking_last_images($limit) {
		global $user;
		$query = "select `imageId`,`name`,`created`, `galleryId` from `tiki_images` order by `created` desc";

		$result = $this->query($query,array(),$limit,0);
		$ret = array();

		while ($res = $result->fetchRow()) {
			if ($this->user_has_perm_on_object($user, $res['galleryId'], 'image gallery', 'tiki_p_view_image_gallery')) {
				$aux["name"] = $res["name"];
				$aux["hits"] = $res["created"];
				$aux["href"] = 'tiki-browse_image.php?imageId=' . $res["imageId"];
				$ret[] = $aux;
			}
		}

		$retval["data"] = $ret;
		$retval["title"] = tra("Wiki last images");
		$retval["y"] = tra("Upload date");
		$retval["type"] = "date";
		return $retval;
	}

	function filegal_ranking_last_files($limit) {
		global $user;
		$query = "select `fileId`,`filename`,`created`, `galleryId` from `tiki_files` order by `created` desc";

		$result = $this->query($query,array(),$limit,0);
		$ret = array();

		while ($res = $result->fetchRow()) {
			if ($this->user_has_perm_on_object($user, $res['galleryId'], 'file gallery', 'tiki_p_view_file_gallery')) {
				$aux["name"] = $res["filename"];
				$aux["hits"] = $res["created"];
				$aux["href"] = 'tiki-download_file.php?fileId=' . $res["fileId"];
				$ret[] = $aux;
			}
		}

		$retval["data"] = $ret;
		$retval["title"] = tra("Wiki last files");
		$retval["y"] = tra("Upload date");
		$retval["type"] = "date";
		return $retval;
	}

	function cms_ranking_top_articles($limit) {
		global $user;
		$query = "select * from `tiki_articles` order by `nbreads` desc";

		$result = $this->query($query,array(),$limit,0);
		$ret = array();

		while ($res = $result->fetchRow()) {
			if ($this->user_has_perm_on_object($user, $res['articleId'], 'article', 'tiki_p_read_article')) {
				$aux["name"] = $res["title"];
				$aux["hits"] = $res["nbreads"];
				$aux["href"] = 'tiki-read_article.php?articleId=' . $res["articleId"];
				$ret[] = $aux;
			}
		}

		$retval["data"] = $ret;
		$retval["title"] = tra("Wiki top articles");
		$retval["y"] = tra("Reads");
		$retval["type"] = "nb";
		return $retval;
	}

	function blog_ranking_top_blogs($limit) {
		global $user;
		$query = "select * from `tiki_blogs` order by `hits` desc";

		$result = $this->query($query,array(),$limit,0);
		$ret = array();

		while ($res = $result->fetchRow()) {
			if ($this->user_has_perm_on_object($user, $res['blogId'], 'blog', 'tiki_p_read_blog')) {
				$aux["name"] = $res["title"];
				$aux["hits"] = $res["hits"];
				$aux["href"] = 'tiki-view_blog.php?blogId=' . $res["blogId"];
				$ret[] = $aux;
			}
		}

		$retval["data"] = $ret;
		$retval["title"] = tra("Most visited blogs");
		$retval["y"] = tra("Visits");
		$retval["type"] = "nb";
		return $retval;
	}

	function blog_ranking_top_active_blogs($limit) {
		global $user;
		$query = "select * from `tiki_blogs` order by `activity` desc";

		$result = $this->query($query,array(),$limit,0);
		$ret = array();

		while ($res = $result->fetchRow()) {
			if ($this->user_has_perm_on_object($user, $res['blogId'], 'blog', 'tiki_p_read_blog')) {
				$aux["name"] = $res["title"];
				$aux["hits"] = $res["activity"];
				$aux["href"] = 'tiki-view_blog.php?blogId=' . $res["blogId"];
				$ret[] = $aux;
			}
		}

		$retval["data"] = $ret;
		$retval["title"] = tra("Most active blogs");
		$retval["y"] = tra("Activity");
		$retval["type"] = "nb";
		return $retval;
	}

	function blog_ranking_last_posts($limit) {
		global $user;
		$query = "select * from `tiki_blog_posts` order by `created` desc";

		$result = $this->query($query,array(),$limit,0);
		$ret = array();

		while ($res = $result->fetchRow()) {
			if ($this->user_has_perm_on_object($user, $res['blogId'], 'blog', 'tiki_p_read_blog')) {
				$q = "select `title` from `tiki_blogs` where `blogId`=?";

				$name = $this->getOne($q,array($res["blogId"]));
				$aux["name"] = $name;
				$aux["hits"] = $res["created"];
				$aux["href"] = 'tiki-view_blog.php?blogId=' . $res["blogId"];
				$ret[] = $aux;
			}
		}

		$retval["data"] = $ret;
		$retval["title"] = tra("Blogs last posts");
		$retval["y"] = tra("Post date");
		$retval["type"] = "date";
		return $retval;
	}

	function wiki_ranking_top_authors($limit, $categ=array()) {
		global $user;
		
		$bindvals = array();
		$mid = '';
		if ($categ) {
			$mid .= " INNER JOIN (`tiki_objects` as tob, `tiki_category_objects` as tco) ON (tp.`pageName` = tob.`itemId` and tob.`objectId` = tco.`catObjectId`) WHERE tob.`type` = 'wiki page' AND (tco.`categId` = ?";
			$bindvals[] = $categ[0]; 	
			for ($i = 1; $i < count($categ); $i++) {
				$mid .= " OR tco.`categId` = " . $categ[$i];
			}
			$mid .= ")";
		}
		$query = "select distinct tp.`user`, count(*) as `numb` from `tiki_pages` tp $mid group by `user` order by ".$this->convertSortMode("numb_desc");

		$result = $this->query($query,$bindvals,$limit,0);
		$ret = array();
		$retu = array();

		while ($res = $result->fetchRow()) {
			$ret["name"] = $res["user"];
			$ret["hits"] = $res["numb"];
			$ret["href"] = "tiki-user_information.php?view_user=".urlencode($res["user"]);
			$retu[] = $ret;
		}
		$retval["data"] = $retu;
		$retval["title"] = tra("Wiki top authors");
		$retval["y"] = tra("Pages");
		$retval["type"] = "nb";
		return $retval;
	}

	function cms_ranking_top_authors($limit) {
		$query = "select distinct `author`, count(*) as `numb` from `tiki_articles` group by `author` order by ".$this->convertSortMode("numb_desc");

		$result = $this->query($query,array(),$limit,0);
		$ret = array();
		$retu = array();

		while ($res = $result->fetchRow()) {
			$ret["name"] = $res["author"];
			$ret["hits"] = $res["numb"];
			$ret["href"] = "tiki-user_information.php?view_user=".urlencode($res["author"]);
			$retu[] = $ret;
		}
		$retval["data"] = $retu;
		$retval["title"] = tra("Top article authors");
		$retval["y"] = tra("Articles");
		$retval["type"] = "nb";
		return $retval;
	}

}
$ranklib = new RankLib;
<?php
 
class WikiLib extends TikiLib {
    
  function WikiLib($db) 
  {
    if(!$db) {
      die("Invalid db object passed to WikiLib constructor");  
    }
    $this->db = $db;  
  }
  
  // This method renames a wiki page
  // If you think this is easy you are very very wrong
  function wiki_rename_page($oldName,$newName)
  {
    if($this->page_exists($newName)) {
      return false;
    }
  	// 1st rename the page in tiki_pages
  	$query = "update tiki_pages set pageName='$newName' where pageName='$oldName'";
  	$this->query($query);
  	// correct pageName in tiki_history
  	$query = "update tiki_history set pageName='$newName' where pageName='$oldName'";
  	$this->query($query);
  	// get pages linking to the old page
  	$query = "select fromPage from tiki_links where toPage='$oldName'";
    $result = $this->query($query);
    while($res = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
      $page = $res['fromPage'];
      print("Examining $page<br/>");
	  $info = $this->get_page_info($page);
	  $data=addslashes(str_replace($oldName,$newName,$info['data']));
	  $query = "update tiki_pages set data='$data' where pageName='$page'";
	  $this->query($query);	  
    }
    
    // correct toPage and fromPage in tiki_links
  	$query = "update tiki_links set fromPage='$newName' where fromPage='$oldName'";
    $this->query($query);	  	
  	$query = "update tiki_links set toPage='$newName' where toPage='$oldName'";
    $this->query($query);	    	
  	
  	// tiki_footnotes change pageName
  	$query = "update tiki_page_footnotes set pageName='$newName' where pageName='$oldName'";
    $this->query($query);	  	  	
  	
  	// tiki_structures change page and parent
  	$query = "update tiki_structures set page='$newName' where page='$oldName'";
    $this->query($query);	  	  	  	
  	
  	// user_bookmarks_urls (url)

  	// user notes (data)

	// Build objectId using 'wiki page' and the name
	$oldId = 'wiki page' + md5($oldName);
	$newId = 'wiki page' + md5($newName);
  	
  	// in tiki_categorized_objects update objId
	$query = "update tiki_categorized_objects set objId='$newId' where objId='$oldId'";
    $this->query($query);	  	  	  	
  	
  	// in tiki_comments update object  
  	$query = "update tiki_comments set object='$newId' where object='$oldId'";
    $this->query($query);	  	  	  	
  	
  	// in tiki_mail_events by object
  	$query = "update tiki_mail_events set object='$newId' where object='$oldId'";
    $this->query($query);	  	  	  	
  	
  	// theme_control_objects(objId,name)
  	$query = "update tiki_control_objects set objId='newId',name='$newName' where objId='$oldId'";
    $this->query($query);	  	  	  	
  	
  	
  }
  
  function save_notepad($user,$title,$data)
  {
    $data = addslashes($data);
    $title = addslashes($data);
    
  }

  // Methods to cache and handle the cached version of wiki pages
  // to prevent parsing large pages.
  function get_cache_info($page)
  {
    $query = "select cache,cache_timestamp from tiki_pages where pageName='$page'";
    $result = $this->query($query);
    $res = $result->fetchRow(DB_FETCHMODE_ASSOC);
    return $res;
  }
  
  function update_cache($page,$data)
  {
    $now = date('U');
    $data = addslashes($data);
    $query = "update tiki_pages set cache='$data', cache_timestamp=$now where pageName='$page'";
    $result = $this->query($query);
    return true;
  }
  
  function get_attachment_owner($attId)
  {
    return $this->getOne("select user from tiki_wiki_attachments where attId=$attId");
  }

  function remove_wiki_attachment($attId)
  {
    global $w_use_dir;
    $path = $this->getOne("select path from tiki_wiki_attachments where attId=$attId");
    if($path) {
      @unlink($w_use_dir.$path);
    }
    $query = "delete from tiki_wiki_attachments where attId='$attId'";
    $result = $this->query($query);
  }

  function wiki_attach_file($page,$name,$type,$size, $data, $comment, $user,$fhash)
  {
    $data = addslashes($data);
    $page = addslashes($page);
    $name = addslashes($name);
    $comment = addslashes(strip_tags($comment));
    $now = date("U");
    $query = "insert into tiki_wiki_attachments(page,filename,filesize,filetype,data,created,downloads,user,comment,path)
    values('$page','$name',$size,'$type','$data',$now,0,'$user','$comment','$fhash')";
    $result = $this->query($query);
  }


  function list_wiki_attachments($page,$offset,$maxRecords,$sort_mode,$find)
  {
    $sort_mode = str_replace("_"," ",$sort_mode);
    if($find) {
      $mid=" where page='$page' and (filename like '%".$find."%')";
    } else {
      $mid=" where page='$page' ";
    }
    $query = "select user,attId,page,filename,filesize,filetype,downloads,created,comment from tiki_wiki_attachments $mid order by $sort_mode limit $offset,$maxRecords";
    $query_cant = "select count(*) from tiki_wiki_attachments $mid";
    $result = $this->query($query);
    $cant = $this->getOne($query_cant);
    $ret = Array();
    while($res = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
      $ret[] = $res;
    }
    $retval = Array();
    $retval["data"] = $ret;
    $retval["cant"] = $cant;
    return $retval;
  }
  
  // Functions for wiki page footnotes
  function get_footnote($user,$page)
  {
    $page = addslashes($page);
    $count = $this->getOne("select count(*) from tiki_page_footnotes where user='$user' and pageName='$page'");
    if(!$count) {
      return '';
    } else {
      return $this->getOne("select data from tiki_page_footnotes where user='$user' and pageName='$page'");
    }
  }
  
  function replace_footnote($user,$page,$data)
  {
    $page=addslashes($page);
    $data=addslashes($data);
    $query = "replace into tiki_page_footnotes(user,pageName,data) values('$user','$page','$data')";
    $this->query($query);
  }

  function remove_footnote($user,$page)
  {
    $page=addslashes($page);
    $query = "delete from tiki_page_footnotes where user='$user' and pageName='$page'";
    $this->query($query);
  }  
  
  function wiki_link_structure()
  {
    $query = "select pageName from tiki_pages order by pageName asc";
    $result = $this->query($query);
    while($res = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
      print($res["pageName"]." ");
      $page = $res["pageName"];
      $query2 = "select toPage from tiki_links where fromPage='$page'";
      $result2 = $this->query($query2);
      $pages=Array();
      while($res2 = $result2->fetchRow(DB_FETCHMODE_ASSOC)) {
        if( ($res2["toPage"]<>$res["pageName"]) && (!in_array($res2["toPage"],$pages)) ) {
          $pages[]=$res2["toPage"];
          print($res2["toPage"]." ");
        }
      }
      print("\n");
    }
  }

  // Get a listing of orphan pages
  
}

$wikilib= new WikiLib($dbTiki);

?>
<?php
require_once('tiki-setup.php');
require_once('lib/tikilib.php'); # httpScheme()
include_once('lib/wiki/histlib.php');

if($rss_wiki != 'y') {
 die;
}
header("content-type: text/xml");
$foo = parse_url($_SERVER["REQUEST_URI"]);
$foo1=str_replace("tiki-wiki_rss.php",$tikiIndex,$foo["path"]);
$foo2=str_replace("tiki-wiki_rss.php","img/tiki.jpg",$foo["path"]);
$home = httpPrefix().$foo1;
$img = httpPrefix().$foo2;
$title = $tikilib->get_preference("title","pepe");
$changes =   $histlib->get_last_changes(999, 0, $max_rss_wiki, $sort_mode = 'lastModif_desc');
//print_r($changes);die;
print('<');
print('?xml version="1.0" ?');
print('>');
?>
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
         xmlns="http://purl.org/rss/1.0/">
<channel rdf:about="<?php echo $home?>">
  <title>Tiki RSS feed for the Wiki</title>
  <link><?php echo $home?></link>
  <description>
    Last modifications to the Wiki.
  </description>
  <image rdf:resource="<?php echo $img?>" />
  <items>
    <rdf:Seq>
      <?php
        // LOOP collecting last changes to the wiki
        foreach($changes["data"] as $chg) {
          print('<rdf:li resource="'.$home.'?page='.$chg["pageName"].'">'."\n");
          print('<title>'.$chg["pageName"].' '.$chg["action"].'</title>'."\n");
          print('<link>'.$home.'?page='.$chg["pageName"].'</link>'."\n");
          $data = $tikilib->date_format($tikilib->get_short_datetime_format(),$chg["lastModif"]);
          if(!empty($chg["comment"])) {
            $comment="(".htmlspecialchars($chg["comment"]).")";
          } else {
            $comment='';
          }
          print('<description>'."[$data] :".$chg["action"]." ".htmlspecialchars($chg["pageName"]).$comment.'</description>'."\n");
          print('</rdf:li>'."\n");
        }        
      ?>
    </rdf:Seq>  
  </items>
</channel>
</rdf:RDF>       
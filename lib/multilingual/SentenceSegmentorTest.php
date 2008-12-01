<?php

error_reporting(E_ALL);

require_once 'PHPUnit/Framework.php';
require_once 'SentenceSegmentor.php';
 
class  SentenceSegmentorTest extends PHPUnit_Framework_TestCase
{

   ////////////////////////////////////////////////////////////////
   // Documentation tests
   //    These tests illustrate how to use this class.
   ////////////////////////////////////////////////////////////////

   public function test_This_is_how_you_create_a_SentenceSegmentor() {
      $segmentor = new SentenceSegmentor();
   }
   
   public function test_this_is_how_you_segment_text_into_sentences() {
      $segmentor = new SentenceSegmentor();
      $text = "hello. world";
      $sentences = $segmentor->segment($text);  
   }

   ////////////////////////////////////////////////////////////////
   // Internal tests
   //    These tests check the internal workings of the class.
   ////////////////////////////////////////////////////////////////


   public function test_segmentation_deals_with_period() {
      $text = "hello brand new. world.";
      $expSentences = array("hello brand new.", " world.");
      $this->do_test_basic_segmentation($text, $expSentences, 
                                     "Segmentation did not deal properly with separation with period.");
   }
   
   public function test_segmentation_deals_with_question_mark() {
      $text = "hello? Anybody home?";
      $expSentences = array("hello?", " Anybody home?");
      $this->do_test_basic_segmentation($text, $expSentences, 
                                     "Segmentation did not deal properly with separation with question mark.");
   }   

   
   public function test_segmentation_deals_with_exclamation_mark() {
      $text = "hello! Anybody home!";
      $expSentences = array("hello!", " Anybody home!");
      $this->do_test_basic_segmentation($text, $expSentences, 
                                     "Segmentation did not deal properly with separation with question mark.");
   }  
   
   public function test_segmentation_deals_with_exclamation_empty_string() {
      $text = "";
      $expSentences = array();
      $this->do_test_basic_segmentation($text, $expSentences, 
                                     "Segmentation did not deal properly with empty string.");
   }        
   
   public function test_segmentation_deals_with_wiki_paragraph_break() {
      $text = "This sentence ends with a period and a newline.\n".
              "This sentence has no period, but ends with a wiki paragraph break\n\n".
              "This is the start of a new paragraph.";
      $expSentences = array("This sentence ends with a period and a newline.", 
                            "\nThis sentence has no period, but ends with a wiki paragraph break\n\n", 
                            "This is the start of a new paragraph.");
      $this->do_test_basic_segmentation($text, $expSentences, 
                                        "Segmentation did not deal properly with wiki paragraph break.");
   
   }

   public function test_segmentation_deals_with_bullet_lists() {
      $text = "This sentence precedes a bullet list.\n".
              "* Bullet 1\n".
              "** Bullet 1-1\n".
              "* Bullet 2\n".
              "After bullet list";
      $expSentences = array("This sentence precedes a bullet list.",
                            "\n",
                            "* Bullet 1\n",
                            "** Bullet 1-1\n",
                            "* Bullet 2\nAfter bullet list");
      $this->do_test_basic_segmentation($text, $expSentences, 
                                     "Segmentation did not deal properly with bullet list.");
   
   }
   
   ////////////////////////////////////////////////////////////////
   // Helper methods
   ////////////////////////////////////////////////////////////////
   
   public function do_test_basic_segmentation($text, $expSentences, $message) {    
      $segmentor = new SentenceSegmentor();
      $sentences = $segmentor->segment($text); 
      $got_sentences_as_string = implode(', ', $sentences);
      $exp_sentences_as_string = implode(', ', $expSentences);
      $this->assertEquals($expSentences, $sentences, 
                          $message."\n".
                          "Segmented sentences differed from expected.\n".
                          "Expected Sentences: $exp_sentences_as_string\n".
                          "Got      Sentences: $got_sentences_as_string\n");      
   }
}

?>
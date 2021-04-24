<?php

class Content_SearchController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {

	       $this->getHelper('layout')->disableLayout();
	       $this->getHelper('viewRenderer')->setNoRender();
        // action body
        echo Zend_Json::encode(array(
 "HC4 : The Republic vrs Kwame Jdan - High Court 4 Kumasi - CC C5/02/12","lydia","Arizona","Arkansas","California","Colorado","Connecticut","Delaware","Florida","Georgia","Hawaii","Idaho","Illinois","Indiana","Iowa","Kansas","Kentucky","Louisiana","Maine","Maryland","Massachusetts","Michigan","Minnesota","Mississippi","Missouri","Montana","Nebraska","Nevada","New Hampshire","New Jersey","New Mexico","New York","North Dakota"
                ));
    }

    public function buildIndexAction()
    {
        // action body
        $LuceneIndex = Zend_Search_Lucene::create(APPLICATION_PATH . '/../data/lucene');

        $doc = new Zend_Search_Lucene_Document();

        $doc->addField(Zend_Search_Lucene_Field::text('Statute', 'Act 286'))
            ->addField(Zend_Search_Lucene_Field::unIndexed('url', '/home'))
            ->addField(Zend_Search_Lucene_Field::text('section', 1))
            ->addField(Zend_Search_Lucene_Field::text('content', 'This section shall take.....'));

        $LuceneIndex->addDocument($doc);
        $LuceneIndex->optimize();
        $LuceneIndex->commit();

    }

    public function searchIndexAction()
    {
        // action body
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();

        $index = Zend_Search_Lucene::open(APPLICATION_PATH . '/../data/lucene');
        //$index->find('section');

        $query = $this->_request->getParam('query');

        $hits = $index->find('section');
        

        $resultArray = array();

        foreach ($hits as $hit) {
            $resultArray[]=$hit->Statute . ' Section' . $hit->section . ' - '. $hit->content;
        }

        echo Zend_Json::encode($resultArray);

    }


}






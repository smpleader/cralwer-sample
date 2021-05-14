<?php 

require_once 'base.php';
/**
 * Fefjob component helper.
 *
 * @since  1.6
 */
class Reader extends ReaderBase
{
    public $start_url = 'https://jobs.deere.com/search/?q=&q2=&locationsearch=&geolocation=&searchby=location&d=5&lat=&lon=&title=&department=&location=';
    public $list_url = 'https://jobs.deere.com/search/?q=&sortColumn=referencedate&sortDirection=desc&searchby=location&d=5&startrow=25';
    public $detail_url = 'https://jobs.deere.com/job/Beijing-Senior-Software-Engineer-Beij-100015/707180000/';
    public $keywords = 'engineer';

    public function start($keywords)
    {
        // start to prase the start_url
        $content = $this->read($this->start_url);
        if($content === false)
        {
            return false;
        }

        $dom = new DOMDocument;
        // avoid warning
        // $dom->strictErrorChecking = false;
        // set error level
        $internalErrors = libxml_use_internal_errors(true);
        $dom->loadHTML( '<?xml encoding="UTF-8">' . $content );
        // Restore error level
        libxml_use_internal_errors($internalErrors);

        $xpath = new DOMXPath( $dom );
        
        $pagination = $xpath->query( '//ul[contains(@class, "pagination")]' );
        $links = $pagination[0]->getElementsByTagName('a'); 

        foreach($links as $element)
        {
            $clss = $element->getAttribute('class');
            if($clss != 'paginationItemFirst' || $clss != 'paginationItemLast')
            {
                $tmp = array();
                $url = $element->getAttribute('href');
                if( strpos($url, '#') === false)
                {
                    // there is title + country + date
                    $tmp['url'] = 'https://jobs.deere.com/search/'.$url;
                    $tmp['tmp'] = '';
                    $tmp['level'] = 1;
                    $this->links[] = $tmp;
                }

            }
        }
    }


    public function find($keywords, $url, $existData)
    {
        $content = $this->read($url);
        if($content === false)
        {
            return 2;
        }

        //if(!is_array($keywords)) $keywords = explode("\n", $keywords);

        $dom = new DOMDocument;
        // avoid warning
        // $dom->strictErrorChecking = false;
        // set error level
        $internalErrors = libxml_use_internal_errors(true);
        $dom->loadHTML( '<?xml encoding="UTF-8">' . $content );
        // Restore error level
        libxml_use_internal_errors($internalErrors);

        $xpath = new DOMXPath( $dom );
        $span = $xpath->query( '//span[contains(@class, "jobTitle")]' );
        //$body = $xpath->query( '//div[contains(@class, "job-summary-section")]' );
        
        foreach($span as $job)
        {
            $tmp = array();
            $a = $job->getElementsByTagName('a'); 
            $tmp['url'] = 'https://jobs.deere.com/'. $a[0]->getAttribute('href');
            $tmp['tmp'] = ['title' => trim($a[0]->nodeValue)];
            $tmp['level'] = 2;

            $this->links[$tmp['url']] = $tmp; 
        }
    }

    public function collect($keywords, $url, $existData)
    {
        $content = $this->read($url);
        if($content === false)
        {
            return 2;
        }

        if(!is_array($keywords)) $keywords = explode("\n", $keywords);

        $dom = new DOMDocument;
        // avoid warning
        // $dom->strictErrorChecking = false;
        // set error level
        $internalErrors = libxml_use_internal_errors(true);
        $dom->loadHTML( '<?xml encoding="UTF-8">' . $content );
        // Restore error level
        libxml_use_internal_errors($internalErrors);

        $xpath = new DOMXPath( $dom );
        
        // need to prepare title, employer, location, pay, jd, expired_at
        $tmp = new StdClass;
             
        $h1 = $xpath->query('//h1[contains(@id, "title")]');
        $tmp->title = trim($h1[0]->nodeValue);

        $tmp->employer = 'John Deere';
 
        $span = $xpath->query('//span[contains(@class, "jobGeoLocation")]');
        $tmp->location = trim($span[0]->nodeValue);
         
        $desc = $xpath->query('//div[contains(@class, "job")]');
        $noneHTML = $desc[0]->nodeValue;
        $tmp->jd = $this->innerHTML($desc[0]);

        $tmp->keywords = array();
        foreach($keywords as $kw)
        {
            /*FefjobHelper::toLogFile('---- Check KW '. $kw, 
                stripos($noneHTML, $kw) ,
                stripos($tmp->title, $kw) ,
                $tmp->title
            );*/
            if( stripos($noneHTML, $kw) !== false ||
                stripos($tmp->title, $kw) !== false )
            {
                $tmp->keywords[] = $kw;
            }
        }

        if(!count($tmp->keywords))
        {
            return 3;
        }

        $tmp->keywords =  implode("\n", $tmp->keywords);
        // can't find information beneath
        $tmp->pay = '';
        $tmp->expired_at = ''; 

        return $tmp;
    }
}

<?php

/**
 * Fefjob component helper.
 *
 * @since  1.6
 */

define('JPATH_CACHE', __DIR__. '/cache');

class ReaderBase
{
    protected $start_url;
    protected $list_url;
    protected $detail_url;

    protected $links = array(); 
    protected $cachePage = true;
    
    protected function innerHTML(DOMNode $element) 
    { 
        $innerHTML = ''; 
        $children  = $element->childNodes;
    
        foreach ($children as $child) 
        { 
            $innerHTML .= $element->ownerDocument->saveHTML($child);
        }
    
        return $innerHTML; 
    } 

    public function getResult()
    {
        return $this->links;
    }

    public function start($keywords)
    {
        // start to prase the start_url
    }

    public function find($keywords, $url, $existData)
    {
        // start to prase the start_url
    }

    public function collect($keywords, $url, $existData)
    {
        // start to prase the start_url
    }

    public function read($url)
    {
        if($this->cachePage)
        {
            $hash = substr(md5($url), 0, 8);
            $cache = JPATH_CACHE.'/fefjob/'.$hash.'.html';
            if(file_exists($cache)){
                return file_get_contents($cache);
            }
        }
        
        $content = $this->request($url);

        if($this->cachePage && $content != false)
        {
            if(!file_exists(JPATH_CACHE.'/fefjob'))
            {
                mkdir(JPATH_CACHE.'/fefjob/', 0755);
            }
            file_put_contents($cache, $content);
        }
        return $content;
    } 

    private static $curl;
    public function request($url, $debug=false)
    {
        if(static::$curl===null)
        {
            static::$curl = curl_init();
        }

        curl_setopt(static::$curl, CURLOPT_URL, $url);
		curl_setopt(static::$curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(static::$curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 11_2_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.192 Safari/537.36');
		if($debug) curl_setopt(static::$curl, CURLOPT_HEADER, true);

        $html = curl_exec(static::$curl);
		$status_code = curl_getinfo(static::$curl, CURLINFO_HTTP_CODE);
		
		if($debug) return $html;

		//static::toLogFile('Called to url ', $url, $status_code);

        if($status_code == 200)
        {
            return $html;
        }
        else
        {
			if($status_code == 301 || $status_code == 302) 
			{
				$html = static::request($url, true);
				
				$matches = array();
				preg_match("/(location:|Location:|URI:)[^(\n)]*/", $html, $matches);
				$u = trim(str_replace($matches[1], '', $matches[0]));
				$url_parsed = parse_url($u);
				//static::toLogFile('URL::', $html, $matches, $u, $url_parsed);
				if(isset($url_parsed)) return static::request($u);
			}

			$msg = 'Read URl '. $url. ' failed with HTTP code '.$status_code;
            var_dump($msg);
            
			return false;
		}
	}
}

/*

    1.) Waupaca
        a. https://www.waupacafoundry.com/en/careers/opportunities
        b. Project Engineer (Internship)
        c. https://jobs.ourcareerpages.com/job/554515?source=WaupacaFoundryInc&jobFeedCode=WaupacaFoundryInc&returnURL=http://www.waupacafoundry.com/en/careers/why-waupaca
    2.) Neenah Foundry
        a. https://www.nfco.com/careers/
        b. Internship – Summer 2021 – Quality Assurance
        c. https://www.nfco.com/careers/internship-summer-2021-quality-assurance/
    3.) Grede
        a. https://grede.com/home-main/careers/jobs/
        b. https://us59.dayforcehcm.com/CandidatePortal/en-US/grede
        c. Foundry Operations Supervisor
        d. https://us59.dayforcehcm.com/CandidatePortal/en-US/grede/Posting/View/270
    4.) American Foundry Society
        a. https://afsinc-jobs.careerwebsite.com/
        b. Sand Foundry Leader
        c. https://afsinc-jobs.careerwebsite.com/job/sand-foundry-leader/54932545/
    5.) McWane
        a. https://www.mcwane.com/careers/
        b. https://careers-mcwane.icims.com/jobs/intro?hashed=-435648188&mobile=false&width=1522&height=500&bga=true&needsRedirect=false&jan1offset=-360&jun1offset=-300
        c. McWane Ductile Ohio – Mechanical Engieering Co-Op Spring Co-Op 2021
        d. https://careers-mcwane.icims.com/jobs/3809/mcwane-ductile-ohio---mechanical-engieering-co-op-spring-co-op-2021/job

*/

/*
    //base url
$base = 'https://play.google.com/store/apps';

$curl = curl_init();
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($curl, CURLOPT_URL, $base);
curl_setopt($curl, CURLOPT_REFERER, $base);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
$str = curl_exec($curl);
curl_close($curl);

// Create a DOM object
$html_base = new simple_html_dom();
// Load HTML from a string
$html_base->load($str);

//get all category links
foreach($html_base->find('a') as $element) {
    echo "<pre>";
    print_r( $element->href );
    echo "</pre>";
}

$html_base->clear(); 
unset($html_base);
*/
    
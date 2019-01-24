<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController ;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use OneForge\ForexQuotes\ForexDataClient;
use Symfony\Component\Cache\Simple\FilesystemCache;

class HomeController extends AbstractController {
    private $defaultcurrency = 'USD';
    private $api_key = 'EQSnBJo9GkXJRdzzoWGAjxD2b7RwUtsS';
    public function index()
    {
        try{
            $cache = new FilesystemCache();
            if(!$cache->has('symbols')){
                if(!($symbols = $this->getcurrency()))
                    throw new Exception('Unknown response format from ForexDataClient');
            }
            else
                $symbols = $cache->get('symbols');
            
            $vars = array(
                'fromsymbol' => array_keys($symbols),
                'tosymbol' => $symbols[$this->defaultcurrency],
                'default' => $this->defaultcurrency,
                'rate' => $this->getrate($this->defaultcurrency,$symbols[$this->defaultcurrency][0])
            );
            return $this->render('frontend/index.html.twig', $vars);
        }
        catch(Exception $e){
            return new Response(
                $e->getMessage()
            );
        }
    }

    public function request(Request $request){
        try{
            if($request->isXMLHttpRequest()){
                $symbols = array();
                $cache = new FilesystemCache();    
                if($cache->has('symbols'))
                    $symbols = $cache->get('symbols');
                if(!isset($symbols[$request->request->get('symbol')]))
                    if(!($symbols = $this->getcurrency()))
                        throw new Exception('Unknown response format from ForexDataClient');
                    elseif(!isset($symbols[$request->request->get('symbol')]))
                        throw new Exception('Currency symbol not exists in ForexDataClient');
                
                return new JsonResponse(array(
                    'rate' => $this->getrate($request->request->get('symbol'),$symbols[$request->request->get('symbol')][0]),
                    'data' => $symbols[$request->request->get('symbol')]
                ));
            }
        }
        catch(Exception $e){
            return new Response(
                $e->getMessage()
            );
        }
    }

    public function rate(Request $request){
        try{
            if($request->isXMLHttpRequest()){
                return new JsonResponse(array(
                    'rate' => $this->getrate($request->request->get('fromsymbol'),$request->request->get('tosymbol'))
                ));
            }
        }
        catch(Exception $e){
            return new Response(
                $e->getMessage()
            );
        }
    }

    private function getcurrency(){
        if($symbols = $this->loadcurrency()){
            $relsymbol = array();
            if(!empty($symbols))
                foreach($symbols as $symbol)
                    if(strlen($symbol)==6)
                        $relsymbol[substr($symbol,0,3)][] = substr($symbol,3,3);
            if(!empty($relsymbol)){
                $cache = new FilesystemCache();
                $cache->set('symbols', $relsymbol);
                return $relsymbol;
            }
        }
        else
            throw new Exception('Could\'t connect to API');
    }

    private function loadcurrency(){
        $client = new ForexDataClient($this->api_key);
        return $client->getSymbols();
    }

    private function getrate($fromcurr,$tocurr){
        $exhanges = array();
        $cache = new FilesystemCache();
        if($cache->has('exhanges')){
            $exhanges = $cache->get('exhanges');
            if(isset($exhanges["{$fromcurr}{$tocurr}"]))
                return $exhanges["{$fromcurr}{$tocurr}"];
        }
        $client = new ForexDataClient($this->api_key);
        if($rate = $client->convert($fromcurr, $tocurr, 1)){
            $exhanges["{$fromcurr}{$tocurr}"] = $rate['value'];
            $cache->set('exhanges', $exhanges);
            return $rate['value'];
        }
    }
}
<?php


namespace App\Service;


use Lavoisier\Hydrators\EntriesHydrator;
use Lavoisier\Query;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LavoisierService
{
    private $lavoisierUrl;
    private $lavoisierPort;
    private $logger;

    /**
     * LavoisierService constructor.
     * @param LoggerInterface $logger
     * @param $lavoisierUrl
     */
    public function __construct(LoggerInterface $logger, $lavoisierUrl,$lavoisierPort)
    {
        $this->lavoisierUrl = $lavoisierUrl;
        $this->lavoisierPort = $lavoisierPort;
        $this->logger = $logger;
    }

    public function query($view, array $array_POST = [], string $filter = null, $messageException = null){
        try {
            $lquery = new Query($this->lavoisierUrl, $view,'lavoisier','xml',$this->lavoisierPort);
            $hydrator = new EntriesHydrator();

            if (isset($filter)){
                $lquery->setPath($filter);
            }
            if (!empty($array_POST)){
                $lquery->setMethod('POST');
                $lquery->setPostFields($array_POST);
            }
            $lquery->setHydrator($hydrator);
            $result = $lquery->execute();
            $data = $result->getArrayCopy();

        } catch (\Exception $e) {
            if (isset($messageException)){

                $this->addFlash(
                    "danger",
                    "$messageException - ".$e->getMessage()
                );
            }else{
                $this->addFlash(
                    "danger",
                    "Can't get List of data  .. Lavoisier call failed - ".$e->getMessage()
                );
            }

            $data = [];
        }

        return $data;
    }

    public function queryVoListMembers($vo){
        try {
            $lquery = new Query($this->lavoisierUrl, 'vo-voms_listMembers-vo');

            $lquery->setMethod('POST');
            $lquery->setPostFields(['vo' => $vo]);

            $result = $lquery->execute();
            $data = simplexml_load_string($result);

        } catch (\Exception $e) {
            $this->logger->emergency("Unable to get Voms users view for VO $vo");
            $this->logger->emergency("error : {$e->getMessage()}");
            $data = [];
        }

        return $data;
    }


    public function notify($view){
        $lquery = new Query($this->lavoisierUrl, $view, 'notify');
        return $lquery->execute();
    }


}

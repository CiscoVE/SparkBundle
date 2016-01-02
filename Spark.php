<?php

namespace CiscoSystems\SparkBundle;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use CiscoSystems\SparkBundle\Event\Room;



class Spark 
{

	
	
	
	
public function createRoom($title = "Default Room Name")
{
	$room = new Room();
	$roomId = $room->createRoom($title);
	return $roomId;
}
	


}
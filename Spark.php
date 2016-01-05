<?php

namespace CiscoSystems\SparkBundle;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use CiscoSystems\SparkBundle\Event\Room;
use CiscoSystems\SparkBundle\Event\Membership;
use CiscoSystems\SparkBundle\Event\Messages;
use CiscoSystems\SparkBundle\Event\People;
use CiscoSystems\SparkBundle\Event\WebHook;


class Spark 
{

	protected $room;
	protected $membership;
	protected $messages;
	protected $people;
	protected $webhook;
	
	
	public function __construct( Room $room, Membership $membership, Messages $messages, People $people, WebHook $webhook )
	{
		$this->room    		= $room;
		$this->membership   = $membership;
		$this->messages     = $messages;
		$this->people       = $people;
		$this->webhook      = $webhook;
	
	}
	
	
	
public function createRoom($title = "Default Room Name", $moderated = FALSE, $defaultUserEmail = '')
{
	if ('' == $defaultUserEmail)
	{ return "Add a default user, or this room will not be accessible"; }
	
	$room = $this->room->createRoom($title);
	
	if ($room->id)
	{
		
		
		
	}
	return $roomId;
}
	


}
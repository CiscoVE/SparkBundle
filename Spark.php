<?php

namespace CiscoSystems\SparkBundle;

use CiscoSystems\SparkBundle\Event\Room;
use CiscoSystems\SparkBundle\Event\Membership;
use CiscoSystems\SparkBundle\Event\Message;
use CiscoSystems\SparkBundle\Event\People;
use CiscoSystems\SparkBundle\Event\WebHook;
use CiscoSystems\SparkBundle\Authentication\Oauth;

/* The Spark Class is meant to store common actions which might be performed through the API */

class Spark 
{

	protected $room;
	protected $membership;
	protected $message;
	protected $people;
	protected $webhook;
	protected $oauth;
	
	
	public function __construct( Room $room, Membership $membership, Message $message, People $people, WebHook $webhook, Oauth $oauth )
	{
		$this->room    		= $room;
		$this->membership   = $membership;
		$this->message      = $message;
		$this->people       = $people;
		$this->webhook      = $webhook;
		$this->oauth        = $oauth;
	
	}
	
	
	/* Description: Function creates a new room, and adds the API user by default.  Setting Moderated to TRUE
	 * sets the API user as moderator.  Adding a defaultUserEmail adds an additional user to the room, which is
	 * highly recommended, or access could be lost to the room (unless you store the resulting room id).
	 * Param  String  $title
	 * Param  Boolean $moderated
	 * Param  String  $defaultUserEmail
	 * return String 
	 */
	public function createRoom($title = "Default Room Name", $moderated = FALSE, $defaultUserEmail = '')
	{
		if ('' == $defaultUserEmail)
		{ return "Add a default user, or this room will not be accessible"; }
		
		$room = $this->room->createRoom($title);
		
		if ($room->id)
		{
			if ('' != $defaultUserEmail){
			$memberOptions = array();
		    $memberOptions['personEmail'] = $defaultUserEmail;
		    $addMember = $this->membership->createMembership($room->id, $memberOptions );
			}
		    
			if (isset($addMember->id) && $moderated == TRUE){
			$this->lockRoom($room->id);
			}
			
		}
		return $room->id;
	}

	/* This function sets a person as moderator by email address.  If email is empty
	 * then the API user will become moderator.  
	 */
	public function lockRoom($sparkId = '', $personEmail = '')
	{
	    $membershipOptions = array();
	    $membershipOptions['roomId'] = $sparkId;
	    if ('' != $personEmail){
	    	$membershipOptions['personEmail'] = $personEmail;
	    } else {
	    	$membershipOptions['personId']    = $this->oauth->getMachineId();
	    }
	    
		$membershipArray = $this->membership->getMemberships($membershipOptions);
		$membershipId    = $membershipArray->items[0]->id;
		
	    return $this->membership->updateMembership($membershipId, TRUE);
	}
	
	/* This function removes a person as moderator by email address.  If email is empty
	 * then the API user will be removed as moderator - if already a moderator.
	 */
	public function unlockRoom($sparkId = '', $personEmail = '')
	{
		$membershipOptions = array();
		$membershipOptions['roomId'] = $sparkId;
		if ('' != $personEmail){
			$membershipOptions['personEmail'] = $personEmail;
		} else {
			$membershipOptions['personId']    = $this->oauth->getMachineId();
		}
		 
		$membershipArray = $this->membership->getMemberships($membershipOptions);
		$membershipId    = $membershipArray->items[0]->id;
	
		return $this->membership->updateMembership($membershipId, FALSE);
	}
	
	/* Description: gets the conversation stream for a given room, where the text key has a value.  If text is empty,
	 * the row may contain a file reference. Files calls will be in a separate function.
	 */
	public function getConversation($sparkId = '', $options = array())
	{
		
		$getMessages = $this->message->getMessages($sparkId, $options );
		
		if (isset($getMessages->items) && count($getMessages->items) > 0)
		{
			$output = array();
			foreach ($getMessages->items as $items)
			{
				if (isset($items->text) && $items->text != '')
				{
					$convo = array();
					$convo['text'] 			= $items->text;
					$convo['created'] 		= $items->created;
					$convo['personEmail'] 	= $items->personEmail;
				$output[] = $convo;
				}
			}
			return $output;	
		} else {
			return $getMessages;
		}
	}
	
	public function renameRoom($sparkId = null, $roomName = null){
		$renameRoom = $this->room->updateRoom( $sparkId, $roomName );
	
		return $renameRoom;
	}
	
	public function addSingleSparkUser( $sparkId, $newUserEmail){
	
		$options = array();
		$options['personEmail'] = $newUserEmail;
		
		$addMember = $this->membership->createMembership( $sparkId, $options);
		return $addMember;
	
	}
	
	public function removeSparkUser( $sparkId, $pid){
	
		$memberOptions = array();
		$memberOptions['roomId'] 	= $sparkId;
		$memberOptions['personId']	= $pid;
		$removeUser = array();
		$mid = $this->membership->getMembership($membershipOptions);
		if (isset($mid->items) && count($mid->items) > 0)
		{
			$removeUser = $this->membership->deleteMembership($mid->items->id);
			
		}

		return $removeUser;
	
	}

}
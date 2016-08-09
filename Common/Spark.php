<?php

namespace CiscoSystems\SparkBundle\Common;

use CiscoSystems\SparkBundle\Event\Room;
use CiscoSystems\SparkBundle\Event\Team;
use CiscoSystems\SparkBundle\Event\Membership;
use CiscoSystems\SparkBundle\Event\TeamMembership;
use CiscoSystems\SparkBundle\Event\Message;
use CiscoSystems\SparkBundle\Event\People;
use CiscoSystems\SparkBundle\Event\WebHook;
use CiscoSystems\SparkBundle\Authentication\Oauth;
use CiscoSystems\SparkBundle\Exception\ApiException;

/* The Spark Class is meant to store common actions which might be performed through the API */

class Spark 
{

	protected $room;
	protected $team;
	protected $membership;
	protected $teammembership;
	protected $message;
	protected $people;
	protected $webhook;
	protected $oauth;
	
	
	public function __construct( Room $room, Team $team, Membership $membership, TeamMembership $teammembership, Message $message, People $people, WebHook $webhook, Oauth $oauth )
	{
		$this->room    			= $room;
		$this->team				= $team;
		$this->membership   	= $membership;
		$this->teammembership 	= $teammembership;
		$this->message      	= $message;
		$this->people       	= $people;
		$this->webhook      	= $webhook;
		$this->oauth        	= $oauth;
	
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
		
		$r	   = $this->room->createRoom($title);
		if (isset($r->statusCode))
		{
			return new Response(array('status'=> 'error', 'errorMessage' => 'Add Room: ' . $r->statusMessage));
		}
		$room  = json_decode($r->getBody());
		
		if ($room->id)
		{
			if ('' != $defaultUserEmail){
			$memberOptions = array();
		    $memberOptions['personEmail'] = $defaultUserEmail;
		    $am	   		= $this->membership->createMembership($room->id, $memberOptions );
		    if (isset($am->statusCode))
		    {
		    	return new Response(array('status'=> 'error', 'errorMessage' => 'Create Membership: ' . $am->statusMessage));
		    }
		    $addMember  = json_decode($am->getBody());
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
	    	$membershipOptions['personId']    = $this->oauth->getStoredMachineUid();
	    }
	    
		$membershipArray = $this->membership->getMembership($membershipOptions);
		if (isset($membershipArray->statusCode))
		{
			return new Response(array('status'=> 'error', 'errorMessage' => 'Lock -> Create Membership: ' . $membershipArray->statusMessage));
		}
		$membershipObj   = json_decode($membershipArray->getBody());
		$membershipId    = $membershipObj->items[0]->id;
		$lr              = $this->membership->updateMembership($membershipId, TRUE);
		if (isset($lr->statusCode))
		{
			return new Response(array('status'=> 'error', 'errorMessage' => 'Lock -> Update Membership: ' . $lr->statusMessage));
		}
		$lockResponse    = json_decode($lr->getBody());
	    return $lockResponse;
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
			$membershipOptions['personId']    = $this->oauth->getStoredMachineUid();
		}
		 
		$membershipArray = $this->membership->getMembership($membershipOptions);
		$membershipObj   = json_decode($membershipArray->getBody());
		$membershipId    = $membershipObj->items[0]->id;
		$lr              = $this->membership->updateMembership($membershipId, FALSE);
		$lockResponse    = json_decode($lr->getBody());
		return $lockResponse;
	}
	
	/* Description: gets the conversation stream for a given room, where the text key has a value.  If text is empty,
	 * the row may contain a file reference. Files calls will be in a separate function.
	 */
	public function getConversation($sparkId = '', $options = array())
	{
		
		$messageObject = $this->message->getMessages($sparkId, $options );
		$getMessages   = json_decode($messageObject->getBody());
		
		if (isset($getMessages->items) && count($getMessages->items) > 0)
		{
			$output = array();
			foreach ($getMessages->items as $items)
			{
				if (isset($items->text) && $items->text != '')
				{
					$convo = array();
					$convo['id']			= $items->id;
					$convo['text'] 			= $items->text;
					$convo['created'] 		= $items->created;
					$convo['personId'] 		= $items->personId;
					$convo['personEmail'] 	= $items->personEmail;
					$convo['roomType'] 		= $items->roomType;
				$output[] = $convo;
				}
			}
			return $output;	
		} else {
			return $getMessages;
		}
	}
	
	public function renameRoom($sparkId = null, $roomName = null){
		$renameRoomObj = $this->room->updateRoom( $sparkId, $roomName );
		$renameRoom    = json_decode($renameRoomObj->getBody());
	
		return $renameRoom;
	}
	
	/* Description: Adds a single user to a given Spark Room.
	 * @Param String $sparkId - The spark room base64 id
	 * @Param Array  $newUserOptions - Options array are: array('personId' => '', 'personEmail' => '', 'isModerator' => FALSE/TRUE)
	 * @Return Array - Contains the personId, email, roomid, createdDate
	 */
	public function addSingleSparkUser( $sparkId, $newUserOptions = array()){
		
		$createMemberObject =  $this->membership->createMembership( $sparkId, $newUserOptions);
		return                 json_decode($createMemberObject->getBody());
	
	}
	
	/* Description: removes a single user to a given Spark Room.
	 * @Param String $sparkId - The room Id containing the user to be removed
	 * @Param Array  $removeUserOptions - Options array are: array('personId' => '', 'personEmail' => ''). 
	 * Add only one of the two keys to reference the user.
	 * @Return Array 
	 */
	public function removeSparkUser( $sparkId, $removeUserOptions = array() ){
	
		$memberOptions = array();
		$memberOptions['roomId'] 	= $sparkId;
		if (isset($removeUserOptions['personId'])){
			$memberOptions['personId'] = $removeUserOptions['personId'];
		}
		if (isset($removeUserOptions['personEmail'])){
			$memberOptions['personEmail'] = $removeUserOptions['personEmail'];
		}

		$removeUser = array();
		$midObj = $this->membership->getMembership($memberOptions);	
		$mid    = json_decode($midObj->getBody());
		if (isset($mid->items[0]->id) )
		{
			$removeUserObj = $this->membership->deleteMembership($mid->items[0]->id);
			$removeUser    = json_decode($removeUserObj->getBody());
			
		}
		return $removeUser;
	}
	
	public function getParticipants($sparkId){
		
		$result     = array();
		$cursor     = NULL;
		$max	    = 200;
		$spark     	= $this->membership;
		
		
		do {
		
			$member = $spark->getMembership(array("roomId" => $sparkId, "cursor" => $cursor, "max" => $max));
			if (isset($member->statusCode)){
				return new Response(array('status'=> $member->statusCode, 'errorMessage' => $member->statusMessage));
			} else {
				if ($member->hasHeader('link')){
					$linkarray = $member->getHeader('link')[0];
					preg_match('~cursor=(.*?)>~', $linkarray, $output);
					$cursor = $output[1];
						
					$body   = json_decode($member->getBody());
					$result = array_merge($result,$body->items);
				} else {
					$body   = json_decode($member->getBody());
					$result = $body->items;
				}
					
					
			}
		} while ($member->hasHeader('link'));
		
		
	return new Response(json_encode($result));
	}
	
	public function getMachineUserId()
	{
		return $this->oauth->getMachinePersonId();
	}
	
	public function getDisplayName($pid)
	{
		$pObj = $this->people->getPersonDetail($pid);
		$p    = json_decode($pObj->getBody());
		return json_encode($p->displayName);
	}

}
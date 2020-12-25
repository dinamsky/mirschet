<?php


interface shopLastmodifiedRequestHandlerConfiguration
{
	public function getType();
	
	public function getFor();
	
	public function getDate();
	
	public function getAllowAgents();
}
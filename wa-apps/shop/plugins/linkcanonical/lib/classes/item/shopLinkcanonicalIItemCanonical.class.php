<?php

interface shopLinkcanonicalIItemCanonical
{
	public function getHash();

	public function getId();

	public function getCanonical();

	/**
	 * @return bool
	 */
	public function hasCanonical();

	public function getStorefront();

	public function getUrl();

	/**
	 * @return bool
	 */
	public function hasUrl();
}
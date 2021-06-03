<?php

/*
 * (c) Andrea Olivato <andrea@lnk.bio>
 *
 * Helper class to structurise the Video object
 *
 * This source file is subject to the GNU General Public License v3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace gimucco\TikTokLoginKit;

class Video {

	private $id;
	private $share_url;
	private $create_time;
	private $cover_image_url;
	private $video_description;
	private $duration;
	private $height;
	private $width;

	/**
	 * Main constructor
	 *
	 * Builds the User Object based on all the parameters provided by the APIs
	 *
	 * @param string $id The  ID of the Video
	 * @param string $share_url The permalink URL of the Video
	 * @param int $create_time Unix Timestamp representation of the creation date/time
	 * @param string $cover_image_url The URL of the cover image (thumbnail of the video)
	 * @param string $video_description The caption of the post, can contain hashtags
	 * @param int $duration Duration of the video (in seconds)
	 * @param int $height Height of the Video (in pixels)
	 * @param int $width Width of the Video (in pixels)
	 * @return void
	 */
	public function __construct(string $id, string $share_url, int $create_time, string $cover_image_url, string $video_description, int $duration, int $height, int $width) {
		$this->id = $id;
		$this->share_url = $share_url;
		$this->create_time = $create_time;
		$this->cover_image_url = $cover_image_url;
		$this->video_description = $video_description;
		$this->duration = $duration;
		$this->height = $height;
		$this->width = $width;
	}

	/**
	 * Alternative Constructor
	 *
	 * Builds the Video Object based on all the parameters provided by the APIs, based on the JSON object
	 *
	 * @param object $json The Video JSON returned by the APIs
	 * @return Video self
	 */
	public static function fromJson(object $json) {
		return new self($json->id, $json->share_url, (int) $json->create_time, $json->cover_image_url, $json->video_description, (int) $json->duration, (int) $json->height, (int) $json->width);
	}

	/**
	 * Get the Video ID
	 * @return string Video ID
	 */
	public function getID() {
		return $this->id;
	}
	/**
	 * Get the URL of the Video
	 * @return string URL of the Video
	 */
	public function getShareURL() {
		return $this->share_url;
	}
	/**
	 * Get the time of creation of the video, in Unix Timestamp
	 * @return int Time of Creation
	 */
	public function getCreateTime() {
		return $this->create_time;
	}
	/**
	 * Get the URL of Cover Image of the video (Thumbnail)
	 * @return string Cover Image URL
	 */
	public function getCoverImageURL() {
		return $this->cover_image_url;
	}
	/**
	 * Get the Caption of the Video
	 * @return string Caption of the Video
	 */
	public function getVideoDescription() {
		return $this->video_description;
	}
	/**
	 * Get the Duration of the video, in seconds
	 * @return int Duration
	 */
	public function getDuration() {
		return $this->duration;
	}
	/**
	 * Get the Height of the Video, in Pixels
	 * @return int Height of the Video
	 */
	public function getHeight() {
		return $this->height;
	}
	/**
	 * Get the Width of the Video, in Pixels
	 * @return int Width of the Video
	 */
	public function getWidth() {
		return $this->width;
	}
}

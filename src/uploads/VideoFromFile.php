<?php

/*
 * (c) Andrea Olivato <andrea@lnk.bio>
 *
 * Helper class to create a Video to Publish on TikTok
 *
 * This source file is subject to the GNU General Public License v3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace gimucco\TikTokLoginKit\uploads;

use Exception;
use gimucco\TikTokLoginKit\Connector;
use gimucco\TikTokLoginKit\response\PublishInfo;

class VideoFromFile {
    private $file;
    private $title;
    private $privacy_level;
    private $comments_off;
    private $duet_off;
    private $stitch_off;
    private $video_cover_timestamp_ms;
    private $bytes;
    private $mime;
    private $upload_url;
    public function __construct(string $file, string $title, string $privacy_level = Connector::PRIVACY_PRIVATE, bool $comments_off = FALSE, bool $duet_off = FALSE, bool $stitch_off = FALSE, int $video_cover_timestamp_ms = 1000) {
        if (!file_exists($file)) {
            throw new Exception('TikTok file to be uploaded doesn\'t exist: '.$file);
        }

        if (!Connector::isValidPrivacyLevel($privacy_level)) {
            throw new Exception('TikTok Invalid Privacy Level Provided: '.$privacy_level.". Must be: ".implode(', ', Connector::VALID_PRIVACY));
        }

        $this->file = $file;
        $this->title = $title;
        $this->privacy_level = $privacy_level;
        $this->comments_off = $comments_off;
        $this->duet_off = $duet_off;
        $this->stitch_off = $stitch_off;
        $this->video_cover_timestamp_ms = $video_cover_timestamp_ms;
        $this->bytes = filesize($this->file);
        $this->mime = mime_content_type($this->file);
    }


    /**
     * Publish a video to TikTok via a Public URL
     * This method validates the privacy, comments, etc, based on the capabilities returned by the CreatorQuery
     * Throws exceptions if the Creator doesn't have the capability available
     *
     * @param Connector $tk
     * @return PublishInfo
     * @throws Exception
     */
    public function publish(Connector $tk) {
        $CreatorQuery = $tk->getCreatorQuery();
        if (!$CreatorQuery->hasPrivacyOption($this->getPrivacyLevel())) {
            throw new Exception('TikTok Error: This Creator cannot publish with the privacy level '.implode(', ', $CreatorQuery->getPrivacyOptions()));
        }

        if ($CreatorQuery->areCommentsOff() && !$this->getCommentsOff()) {
            throw new Exception('TikTok Error: This Creator cannot publish without turning off the Comments');
        }

        if ($CreatorQuery->isDuetOff() && !$this->getDuetOff()) {
            throw new Exception('TikTok Error: This Creator cannot publish without turning off Duet');
        }

        if ($CreatorQuery->isStitchOff() && !$this->getStitchOff()) {
            throw new Exception('TikTok Error: This Creator cannot publish without turning off Stitch');
        }

        return $this->publishWithoutChecks($tk);
    }

    /**
     * Publish to TikTok by automatically replacing any invalid value based on the Creator's capabilities
     * Warning: this changes the privacy, comment settings, without telling you anything
     * The recommended way is to use the `publish` method and manage exceptions.
     *
     * @param Connector $tk
     * @return PublishInfo
     * @throws Exception
     */
    public function publishReplacingInvalidValues(Connector $tk) {
        $CreatorQuery = $tk->getCreatorQuery();
        if (!$CreatorQuery->hasPrivacyOption($this->getPrivacyLevel())) {
            $this->setPrivacyLevel(Connector::PRIVACY_PRIVATE);
        }

        if ($CreatorQuery->areCommentsOff() && !$this->getCommentsOff()) {
            $this->setCommentsOff(TRUE);
        }

        if ($CreatorQuery->isDuetOff() && !$this->getDuetOff()) {
            $this->setDuetOff(TRUE);
        }

        if ($CreatorQuery->isStitchOff() && !$this->getStitchOff()) {
            $this->setStitchOff(TRUE);
        }

        return $this->publishWithoutChecks($tk);
    }

    /**
     * Directly publish to TikTok without performing any checks on the Creator's capabilities.
     * Warning: This is only recommended if you had previously checked and don't want to repeat the same checks.
     *
     * @param Connector $tk
     * @return PublishInfo
     * @throws Exception
     */
    public function publishWithoutChecks(Connector $tk) {
        try {
            $data = [
                'post_info' => [
                    'title' => $this->getTitle(),
                    'privacy_level' => $this->getPrivacyLevel(),
                    'disable_comment' => $this->getCommentsOff(),
                    'disable_duet' => $this->getDuetOff(),
                    'disable_stitch' => $this->getStitchOff(),
                    'video_cover_timestamp_ms' => $this->getVideoCoverTimestampMs()
                ],
                'source_info' => [
                    "source" => "FILE_UPLOAD",
                    "video_size" => $this->getBytes(),
                    "chunk_size" => $this->getBytes(),
                    "total_chunk_count" => 1
                ]
            ];
            $res = $tk->postWithAuth(Connector::BASE_POST_PUBLISH, $data);
            if (!$res) {
                throw new Exception('TikTok Api Error, invalid returned value '.var_export($res, 1));
            }

            $res = json_decode($res);
            if (!$res) {
                throw new Exception('TikTok Api Error, invalid JSON '.$res);
            }

            $publishInfo = PublishInfo::fromJSON($res);
            if (!$publishInfo->getUploadUrl()) {
                throw new Exception('TikTok Api Error, invalid Upload. Error: '.$publishInfo->getErrorCode()." - ".$publishInfo->getErrorMessage());
            }

            $tk->checkPublishStatus($publishInfo->getPublishID());
            $this->setUploadUrl($publishInfo->getUploadUrl());
            $this->performUpload();
            return $publishInfo;
        } catch (Exception $e) {
            throw new Exception('TikTok Api Error: '.$e->getMessage());
        }
    }

    /**
     * Get the path of the file to upload
     *
     * @return string file
     */
    public function getFile() {
        return $this->file;
    }

    /**
     * Get the Title to be added to the Video
     *
     * @return string title
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Get the Privacy Level of the Video
     *
     * @return string privacy level
     */
    public function getPrivacyLevel() {
        return $this->privacy_level;
    }

    /**
     * Get if the Comments are turned Off. True means they are off.
     *
     * @return boolean comments off
     */
    public function getCommentsOff() {
        return $this->comments_off;
    }

    /**
     * Get if Duet is turned Off. True means it's off.
     *
     * @return boolean due off
     */
    public function getDuetOff() {
        return $this->duet_off;
    }

    /**
     * Get if the Stitch is turned Off. True means it's off.
     *
     * @return boolean stitch off
     */
    public function getStitchOff() {
        return $this->stitch_off;
    }

    /**
     * Milliseconds at which to take the Video Cover from the video
     *
     * @return integer milliseconds
     */
    public function getVideoCoverTimestampMs() {
        return $this->video_cover_timestamp_ms;
    }

    /**
     * Get the size of the file in bytes
     *
     * @return integer bytes
     */
    public function getBytes() {
        return $this->bytes;
    }

    /**
     * Get the mime type requested for the Content-Type header
     * E.g. video/mp4
     *
     * @return string mime
     */
    public function getMime() {
        return $this->mime;
    }

    /**
     * Get the temporary upload url
     *
     * @return string mime
     */
    private function getUploadUrl() {
        return $this->upload_url;
    }

    /**
     * Sets the temporary upload url
     *
     * @param string $upload_url returned from the publish endpoint
     * @return void
     */
    private function setUploadUrl(string $upload_url) {
        $this->upload_url = $upload_url;
    }

    /**
     * Sets the Privacy Level
     *
     * @param string $privacy_level
     * @return void
     * @throws Exception
     */
    public function setPrivacyLevel(string $privacy_level) {
        if (!Connector::isValidPrivacyLevel($privacy_level)) {
            throw new Exception('TikTok Invalid Privacy Level Provided: '.$privacy_level.". Must be: ".implode(', ', Connector::VALID_PRIVACY));
        }

        $this->privacy_level = $privacy_level;
    }

    /**
     * Set if the comments are turned off. True = off
     *
     * @param boolean $comments_off
     * @return void
     */
    public function setCommentsOff(bool $comments_off) {
        $this->comments_off = $comments_off;
    }

    /**
     * Set if Duet is turned off. True = off
     *
     * @param boolean $duet_off
     * @return void
     */
    public function setDuetOff(bool $duet_off) {
        $this->duet_off = $duet_off;
    }

    /**
     * Set if Stitch is turned off. True = off
     *
     * @param boolean $stitch_off
     * @return void
     */
    public function setStitchOff(bool $stitch_off) {
        $this->stitch_off = $stitch_off;
    }

    /**
     * Set Milliseconds at which to take the Video Cover from the video
     *
     * @param integer $milliseconds
     * @return void
     */
    public function setVideoCoverTimestampMs(int $milliseconds) {
        $this->video_cover_timestamp_ms = $milliseconds;
    }

    private function performUpload() {
        $headers = [
            'Content-Range: bytes 0-'.($this->getBytes() - 1).'/'.$this->getBytes(),
            'Content-Length: '.$this->getBytes(),
            'Content-Type: '.$this->getMime()
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getUploadUrl());
        curl_setopt($ch, CURLOPT_PUT, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        $fileHandle = fopen($this->getFile(), 'rb');
        curl_setopt($ch, CURLOPT_INFILE, $fileHandle);
        curl_setopt($ch, CURLOPT_INFILESIZE, $this->getBytes());

        curl_exec($ch);
        curl_close($ch);
        fclose($fileHandle);
    }
}

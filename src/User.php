<?php

/*
 * (c) Andrea Olivato <andrea@lnk.bio>
 *
 * Helper class to structure the User object
 *
 * This source file is subject to the GNU General Public License v3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace gimucco\TikTokLoginKit;

class User {
    private $open_id;
    private $union_id;
    private $avatar;
    private $avatar_thumb;
    private $avatar_larger;
    private $display_name;
    private $bio;
    private $url;
    private $is_verified;
    private $followers;
    private $following;
    private $likes;
    private $num_videos;
    private $handle;

    /**
     * Main constructor
     *
     * Builds the User Object based on all the parameters provided by the APIs
     *
     * @param string  $open_id       The Open ID of the user (based on the specific application)
     * @param string  $union_id      The Union ID of the user (based on the developer account)
     * @param string  $avatar        The profile picture of the user
     * @param string  $avatar_larger The profile picture of the user, in better resolution
     * @param string  $display_name  The Display Name (not the Username!!) of the user
     * @param string  $avatar_thumb  The 100x100 thumbnail picture
     * @param string  $bio           The biography text of the user
     * @param string  $url           The URL of the profile of the user (deeplink)
     * @param boolean $is_verified   If the user has the verified checkmark or not
     * @param integer $followers     Number of followers of the user
     * @param integer $following     Number of people that the user follows
     * @param integer $likes         Number of total likes received by the User
     * @param integer $num_videos    Number of Videos published by the User
     * @param string  $handle        @username of the User
     * @return void
     */
    public function __construct(string $open_id, string $union_id, string $avatar, string $avatar_larger, string $display_name, string $avatar_thumb, string $bio, string $url, bool $is_verified, int $followers, int $following, int $likes, int $num_videos, string $handle) {
        $this->open_id = $open_id;
        $this->union_id = $union_id;
        $this->avatar = $avatar;
        $this->avatar_larger = $avatar_larger;
        $this->display_name = $display_name;
        $this->avatar_thumb = $avatar_thumb;
        $this->bio = $bio;
        $this->url = $url;
        $this->is_verified = $is_verified;
        $this->followers = $followers;
        $this->following = $following;
        $this->likes = $likes;
        $this->num_videos = $num_videos;
        $this->handle = $handle;
    }

    /**
     * Alternative Constructor
     *
     * Builds the User Object based on all the parameters provided by the APIs, based on the JSON object
     *
     * @param object $json The user JSON returned by the APIs
     * @return User self
     */
    public static function fromJson(object $json, bool $get_username_remote = FALSE) {
        $open_id = '';
        $union_id = '';
        $avatar = '';
        $avatar_larger = '';
        $display_name = '';
        $avatar_thumb = '';
        $bio = '';
        $url = '';
        $is_verified = FALSE;
        $followers = 0;
        $following = 0;
        $likes = 0;
        $num_videos = 0;
        $handle = '';
        if (!empty($json->data->user->open_id)) {
            $open_id = $json->data->user->open_id;
        }

        if (!empty($json->data->user->union_id)) {
            $union_id = $json->data->user->union_id;
        }

        if (!empty($json->data->user->avatar_url)) {
            $avatar = $json->data->user->avatar_url;
        }

        if (!empty($json->data->user->avatar_larger)) {
            $avatar_larger = $json->data->user->avatar_larger;
        }

        if (!empty($json->data->user->display_name)) {
            $display_name = $json->data->user->display_name;
        }

        if (!empty($json->data->user->avatar_url_100)) {
            $avatar_thumb = $json->data->user->avatar_url_100;
        }

        if (!empty($json->data->user->bio_description)) {
            $bio = $json->data->user->bio_description;
        }

        if (!empty($json->data->user->profile_deep_link)) {
            $url = $json->data->user->profile_deep_link;
        }

        if (!empty($json->data->user->is_verified) && $json->data->user->is_verified) {
            $is_verified = (bool) $json->data->user->is_verified;
        }

        if (!empty($json->data->user->follower_count)) {
            $followers = (int) $json->data->user->follower_count;
        }

        if (!empty($json->data->user->following_count)) {
            $following = (int) $json->data->user->following_count;
        }

        if (!empty($json->data->user->likes_count)) {
            $likes = (int) $json->data->user->likes_count;
        }

        if (!empty($json->data->user->video_count)) {
            $num_videos = (int) $json->data->user->video_count;
        }

        if ($get_username_remote && $url) {
            $profile_url = self::getProfileUrl($url);
            $handle = self::parseHandleFromUrl($profile_url);
        }

        return new self($open_id, $union_id, $avatar, $avatar_larger, $display_name, $avatar_thumb, $bio, $url, $is_verified, $followers, $following, $likes, $num_videos, $handle);
    }

    /**
     * Get the Open ID
  *
     * @return string Open ID
     */
    public function getOpenID() {
        return $this->open_id;
    }
    /**
     * Get the Union ID
  *
     * @return string Union ID
     */
    public function getUnionID() {
        return $this->union_id;
    }
    /**
     * Get the Avatar
  *
     * @return string Avatar URL
     */
    public function getAvatar() {
        return $this->avatar;
    }
    /**
     * Get the Larger Avatar
  *
     * @return string Larger Avatar URL
     */
    public function getAvatarLarger() {
        return $this->avatar_larger;
    }
    /**
     * Get the Display Name
  *
     * @return string Display Name
     */
    public function getDisplayName() {
        return $this->display_name;
    }
    /**
     * Get the 100x100 thumbnail of the Avatar
  *
     * @return string Avatar Thumbnail URL
     */
    public function getAvatarThumb() {
        return $this->avatar_thumb;
    }
    /**
     * Get the Biography Text
  *
     * @return string Biography Text
     */
    public function getBio() {
        return $this->bio;
    }
    /**
     * Get the Deeplinked Profile Url
  *
     * @return string Profile Url
     */
    public function getUrl() {
        return $this->url;
    }
    /**
     * Check if the user is Verified
  *
     * @return boolean is verified
     */
    public function isVerified() {
        return $this->is_verified;
    }
    /**
     * Get the number of followers of the user
  *
     * @return integer Number of Followers
     */
    public function getFollowers() {
        return $this->followers;
    }
    /**
     * Get the number of people the User follows
  *
     * @return integer Number of Following
     */
    public function getFollowing() {
        return $this->following;
    }
    /**
     * Get the number of likes of the user
  *
     * @return integer Number of Likes
     */
    public function getLikes() {
        return $this->likes;
    }
    /**
     * Get the number of videos published by the user
  *
     * @return integer Number of Videos
     */
    public function getNumVideos() {
        return $this->num_videos;
    }
    /**
     * Get the @username of the User
  *
     * @return string Handle
     */
    public function getHandle() {
        return $this->handle;
    }
    /**
     * Get the Best version for the Avatar
  *
     * @return string Avatar URL
     */
    public function getBestAvatar() {
        if ($this->getAvatarLarger()) {
            return $this->getAvatarLarger();
        }

        if ($this->getAvatar()) {
            return $this->getAvatar();
        }

        if ($this->getAvatarThumb()) {
            return $this->getAvatarThumb();
        }

        return '';
    }

    private static function getProfileUrl(string $url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_exec($ch);
        $redirectURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        return $redirectURL;
    }

    private static function parseHandleFromUrl(string $url) {
        preg_match('@www.tiktok.com%2F%40([^%]+)@', $url, $m);
        if ($m && !empty($m[1])) {
            return trim($m[1]);
        }

        preg_match('@www.tiktok.com/\@([^\?]+)@', $url, $m);
        if ($m && !empty($m[1])) {
            return trim($m[1]);
        }

        return '';
    }
}

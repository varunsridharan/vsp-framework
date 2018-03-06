<?php
/**
 * Project: wp-admin-notices
 * File: WP_Notice.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 1/11/2015
 * Time: 8:31 μμ
 * Since: 2.0.0
 * Copyright: 2015 Panagiotis Vagenas
 */

/**
 * Abstract class of a notice
 *
 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
 */
class VSP_WP_Notice {
    /**
     * Notice Type Error
     */
    const TYPE_ERROR = 'error';
    /**
     * Notice Type Updated
     */
    const TYPE_UPDATED = 'updated';
    /**
     *  Notice Type Updated Nag
     */
    const TYPE_UPDATED_NAG = 'update-nag';
    /**
     * Notice message to be displayed
     *
     * @var string
     */
    protected $content;

    /**
     * Title of the notice. This is optional.
     *
     * @var string
     */
    protected $title = '';

    /**
     * Notice type
     *
     * @var string
     */
    protected $type;

    /**
     * In which screens the notice to be displayed
     *
     * @var array
     */
    protected $screens;

    /**
     * Unique identifier for notice
     *
     * @var int
     */
    protected $id;

    /**
     * Number of times to be displayed
     *
     * @var int
     */
    protected $times = 1;

    /**
     * Array index are the user ids this notice should be displayed, values are
     * the displayed times for each user.
     *
     * Index `0` yields total displayed times
     *
     * `[
     *      0 => $totalDisplayedTimes
     *      $userId => $displayedTimesForUser
     * ]`
     *
     * @var array
     */
    protected $users = array();

    /**
     * @var VSP_WP_Admin_Notice_Interface
     */
    protected $formatter;
    /**
     * @var bool
     */
    protected $sticky = FALSE;

    /**
     *
     * @param string $content Content to be displayed
     * @param string $title Title of the notice, optional default is empty string.
     * @param string $type Type of the notice, must be one of {@link self::TYPE_UPDATED}, {@link self::TYPE_ERROR},
     *                        {@link self::TYPE_UPDATED_NAG}. Defaults to {@link self::TYPE_UPDATED}.
     * @param int    $times How many times this notice will be displayed
     * @param array  $screens The admin screen ids this notice will be displayed into (empty for all screens)
     * @param array  $users Array of user ids this notice concerns (empty for all users)
     */
    public function __construct($content = '', $title = '', $type = self::TYPE_UPDATED, $times = 1, $screens = array(), $users = array()) {
        $this->id = uniqid(md5($content), TRUE);

        $this->setContent($content);
        $this->setTitle($title);
        $this->setScreens((array) $screens);
        $this->setTimes($times);

        foreach( $users as $userId ) {
            $this->addUser($userId);
        }
        $this->addUser(0);

        if( ! in_array($type, array( self::TYPE_UPDATED_NAG, self::TYPE_UPDATED, self::TYPE_ERROR )) ) {
            $type = self::TYPE_UPDATED;
        }
        $this->type = $type;
    }

    /**
     * @param $userId
     *
     * @return $this
     * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
     * @since  TODO ${VERSION}
     */
    public function addUser($userId) {
        $userId = (int) $userId;

        if( ! $this->hasUser($userId) ) {
            $this->users[$userId] = 0;
        }

        return $this;
    }

    /**
     * @param $userId
     *
     * @return bool
     * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
     * @since  TODO ${VERSION}
     */
    public function hasUser($userId) {
        return array_key_exists((int) $userId, $this->users);
    }

    public function addUsers($users) {
        if( is_array($users) ) {
            foreach( $users as $userID ) {
                $this->addUser($userID);
            }

        }
    }

    /**
     * Get the content of the notice
     *
     * @return string Formatted content
     */
    public function getContentFormatted() {
        if( ! $this->formatter ) {
            $this->formatter = $this->getDefaultFormatter();
        }

        return $this->formatter->formatOutput($this);
    }

    /**
     * @return VSP_WordPress_Notice|VSP_WordPress_Sticky_Notice
     * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
     * @since  TODO ${VERSION}
     */
    protected function getDefaultFormatter() {
        if( $this->isSticky() ) {
            return new VSP_WordPress_Sticky_Notice();
        }

        return new VSP_WordPress_Notice();
    }

    /**
     * @return boolean
     * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
     * @since  TODO ${VERSION}
     */
    public function isSticky() {
        return $this->sticky;
    }

    /**
     * @param boolean $sticky
     *
     * @return $this
     * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
     * @since  TODO ${VERSION}
     */
    public function setSticky($sticky) {
        $this->sticky = (bool) $sticky;

        return $this;
    }

    /**
     * Get the notice string un-formatted
     *
     * @return string
     */
    public function getContent() {
        return $this->content;
    }

    /**
     *
     * @param string $content
     *
     * @return $this
     */
    public function setContent($content) {
        $this->content = (string) $content;

        return $this;
    }

    /**
     * Increment displayed times of the notice
     *
     * @return $this
     */
    public function incrementDisplayedTimes() {
        $this->users[0]++;

        $userId = get_current_user_id();

        $this->users[$userId] = $this->maybeInitDisplayedToUsers($userId) + 1;

        return $this;
    }

    /**
     * Initializes value in {@link $this::displayedToUsers} for $userId
     *
     * @param int $userId
     *
     * @return int The current value for the specified user after initialization
     * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
     * @since  2.0.1
     */
    public function maybeInitDisplayedToUsers($userId) {
        if( ! $this->hasUser($userId) ) {
            $this->addUser($userId);
        }

        return $this->users[$userId];
    }

    /**
     * @return int
     * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
     * @since  TODO ${VERSION}
     */
    public function getDisplayedTimes() {
        return $this->getDisplayedTimesForUser(0);
    }

    /**
     * @param $userId
     *
     * @return int
     * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
     * @since  TODO ${VERSION}
     */
    public function getDisplayedTimesForUser($userId) {
        return $this->hasUser($userId) ? $this->users[$userId] : 0;
    }

    /**
     * @param int $userId
     *
     * @return bool
     * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
     * @since  TODO ${VERSION}
     */
    public function exceededMaxTimesToDisplayForUser($userId) {
        $userId = (int) $userId;

        if( $this->hasUser($userId) && $this->users[$userId] < $this->times ) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * @return bool
     * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
     * @since  TODO ${VERSION}
     */
    public function exceededMaxTimesToDisplay() {
        $timesCounter = 0;

        foreach( $this->getUsers() as $userTimes ) {
            if( $userTimes >= $this->times ) {
                $timesCounter++;
            }
        }

        return $timesCounter >= $this->countUsers();
    }

    /**
     *
     * @return array
     */
    public function getUsers() {
        $users = $this->users;
        unset($users[0]);

        return $users;
    }

    /**
     * @return int
     * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
     * @since  TODO ${VERSION}
     */
    public function countUsers() {
        return count($this->users) - 1;
    }

    /**
     * @param $userId
     *
     * @return $this
     * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
     * @since  TODO ${VERSION}
     */
    public function removeUser($userId) {
        $userId = (int) $userId;

        if( $userId > 0 && $this->hasUser($userId) ) {
            unset($this->users[$userId]);
        }

        return $this;
    }

    /**
     * Get the screens for the notice to be displayed
     *
     * @return string Current screens slug
     */
    public function getScreens() {
        return $this->screens;
    }

    /**
     * Set the screens the notice will be displayed
     *
     * @param array $screens
     *
     * @return $this
     */
    public function setScreens($screens) {
        $this->screens = (array) $screens;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     *
     * @return int
     */
    public function getTimes() {
        return $this->times;
    }

    /**
     *
     * @param int $times
     *
     * @return $this
     */
    public function setTimes($times) {
        $this->times = (int) $times;

        return $this;
    }

    /**
     * @return string
     * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
     * @since  2.0.0
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
     * @since  2.0.0
     */
    public function setTitle($title) {
        $this->title = (string) $title;

        return $this;
    }

    /**
     * @return VSP_WP_Admin_Notice_Interface
     * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
     * @since  2.0.0
     */
    public function getFormatter() {
        return $this->formatter;
    }

    /**
     * @param VSP_WP_Admin_Notice_Interface $formatter
     *
     * @return $this
     * @throws \InvalidArgumentException If $formatter isn't an instanceof {@link
     *                                  VSP_WP_Admin_Notice_Interface}
     * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
     * @since  2.0.0
     */
    public function setFormatter($formatter) {
        if( ! ( $formatter instanceof VSP_WP_Admin_Notice_Interface ) ) {
            throw new \InvalidArgumentException('Notice VSP_WP_Admin_Notice_Interface must be an instance of VSP_WP_Admin_Notice_Interface');
        }
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * @return string
     * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
     * @since  2.0.0
     */
    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        $this->type = $type;
    }
}
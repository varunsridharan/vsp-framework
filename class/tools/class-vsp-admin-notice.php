<?php
if( ! defined("ABSPATH") ) {
    exit;
}

if( ! class_exists("VSP_Admin_Notice") ) {
    class VSP_Admin_Notice {
        protected static $_instance;

        public static function instance() {
            if( NULL == self::$_instance ) {
                self::$_instance = new self;
            }
            return self::$_instance;
        }

        public function __construct($options = array()) {
            $defaults = array(
                'noticeArrayName' => 'VSP_ADMIN_NOTICES',
                'requestID'       => 'vsp-msg',
            );

            $options = wp_parse_args($options, $defaults);
            $this->noticesArrayName = $options['noticeArrayName'];
            $this->requestID = $options['requestID'];
            $this->notices = array();
            $this->loadNotices();
            $this->auto_remove_Notice();
            if( vsp_is_admin() || vsp_is_ajax() ) {
                add_action('admin_notices', array( $this, 'displayNotices' ));
            }
        }

        public function displayNotices() {
            foreach( $this->notices as $key => $notice ) {
                if( $this->isTimeToDisplay($notice) ) {
                    echo $notice->getContentFormated($notice->getWrapper());
                    $notice->incrementDisplayedTimes();
                }
                if( $notice->getTimes() > 0 ) {
                    if( $notice->isTimeToDie() ) {
                        unset($this->notices[$key]);
                    }
                }
            }
            $this->storeNotices();
        }

        public function auto_remove_Notice() {
            if( isset($_REQUEST[$this->requestID]) ) {
                $nonce = $_REQUEST['_wpnonce'];
                $this->deleteNotice($_REQUEST[$this->requestID]);
                if( wp_get_referer() ) {
                    wp_safe_redirect(wp_get_referer());
                }
            }
        }

        public function deleteNotice($notId) {
            foreach( $this->notices as $key => $notice ) {
                if( $notice->getId() === $notId ) {
                    unset($this->notices[$key]);
                    break;
                }
            }
            $this->storeNotices();
        }

        public function addNotice(VSP_Admin_Notices $notice) {
            $this->notices[] = $notice;
            $this->storeNotices();
        }

        private function loadNotices() {
            $notices = get_option($this->noticesArrayName);
            if( is_array($notices) ) {
                $this->notices = $notices;
            }
        }

        private function storeNotices() {
            update_option($this->noticesArrayName, $this->notices);
        }

        private function isTimeToDisplay(VSP_Admin_Notices $notice) {
            $screens = $notice->getScreen();
            if( ! empty($screens) ) {
                $curScreen = get_current_screen();
                if( ! is_array($screens) || ! in_array($curScreen->id, $screens) ) {
                    return FALSE;
                }
            }

            $usersArray = $notice->getUsers();
            if( ! empty($usersArray) ) {
                $curUser = get_current_user_id();
                if( ! is_array($usersArray) || ! in_array($curUser, $usersArray) || $usersArray[$curUser] >= $notice->getTimes() ) {
                    return FALSE;
                }
            } else if( $notice->getTimes() == 0 ) {
                return TRUE;
            } else if( $notice->getTimes() <= $notice->getDisplayedTimes() ) {
                return FALSE;
            }

            return TRUE;
        }
    }
}

if( ! class_exists("VSP_Admin_Notices") ) {
    abstract class VSP_Admin_Notices {
        protected $content;
        protected $type;
        protected $screen;
        protected $id;
        protected $times = 1;
        protected $users = array();
        protected $displayedTimes = 0;
        protected $displayedToUsers = array();
        protected $WithWraper = TRUE;
        protected $is_dismissible = TRUE;

        public function __construct($content = '', $id = '', $times = 1, $screen = array(), $users = array(), $WithWraper = TRUE) {
            $this->content = $content;
            $this->screen = $screen;
            $this->id = ( empty($id) ) ? uniqid() : $id;
            $this->times = $times;
            $this->users = $users;
            $this->WithWraper = $WithWraper;
        }

        public function getContentFormated($wrapInParTag = TRUE) {
            $class = $this->type;
            $extrC = '';
            if( $this->is_dismissible ) {
                $class .= ' notice is-dismissible';
            }
            $before = '<div id="vsp_notices_' . $this->id . '"  class="' . $class . '">';
            $before .= $wrapInParTag ? '<p>' : '';
            $after = $wrapInParTag ? '</p>' : '';
            $after .= '</div>';
            return $before . $this->getContent() . $after . $extrC;
        }

        public function incrementDisplayedTimes() {
            $this->displayedTimes++;
            if( array_key_exists(get_current_user_id(), $this->displayedToUsers) ) {
                $this->displayedToUsers[get_current_user_id()]++;
            } else {
                $this->displayedToUsers[get_current_user_id()] = 1;
            }
            return $this;
        }

        public function isTimeToDie() {
            if( empty($this->users) ) {
                return $this->displayedTimes >= $this->times;
            } else {
                $i = 0;
                foreach( $this->users as $key => $value ) {
                    if( isset($this->displayedToUsers[$value]) && $this->displayedToUsers[$value] >= $this->times ) {
                        $i++;
                    }
                }
                if( $i >= count($this->users) ) {
                    return TRUE;
                }
            }
            return FALSE;
        }

        public function getWrapper() {
            return $this->WithWraper;
        }

        public function setWrapper($wrapper = TRUE) {
            $this->WithWraper = $wrapper;
            return $this;
        }

        public function getScreen() {
            return $this->screen;
        }

        public function setScreen($screen) {
            $this->screen = $screen;
            return $this;
        }

        public function getContent() {
            return $this->content;
        }

        public function setContent($content) {
            $this->content = $content;
            return $this;
        }

        public function getId() {
            return $this->id;
        }

        public function set_id($id = '') {
            $this->id = $id;
            return $this;
        }

        public function getTimes() {
            return $this->times;
        }

        public function getUsers() {
            return $this->users;
        }

        public function setTimes($times) {
            $this->times = $times;
            return $this;
        }

        public function setUsers(Array $users) {
            $this->users = $users;
            return $this;
        }

        public function getDisplayedTimes() {
            return $this->displayedTimes;
        }

        public function getDisplayedToUsers() {
            return $this->displayedToUsers;
        }

        public function setDisplayedTimes($displayedTimes) {
            $this->displayedTimes = $displayedTimes;
            return $this;
        }

        public function setDisplayedToUsers(Array $displayedToUsers) {
            $this->displayedToUsers = $displayedToUsers;
            return $this;
        }

    }
}

if( ! class_exists("VSP_Admin_Notices_Error") ) {
    class VSP_Admin_Notices_Error extends VSP_Admin_Notices {
        protected $type = 'error';
    }
}
if( ! class_exists("VSP_Admin_Notices_Updated") ) {
    class VSP_Admin_Notices_Updated extends VSP_Admin_Notices {
        protected $type = 'updated';
    }
}
if( ! class_exists("VSP_Admin_Notices_UpdateNag") ) {
    class VSP_Admin_Notices_UpdateNag extends VSP_Admin_Notices {
        protected $type = 'update-nag';
    }
}
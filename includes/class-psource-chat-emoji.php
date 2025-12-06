<?php
/**
 * PSource Chat Emoji System
 * 
 * Modular emoji picker with category support and modern UI
 * 
 * @package PSource Chat
 * @since 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class PSource_Chat_Emoji {
    
    /**
     * Emoji categories with their emojis
     * 
     * @var array
     */
    private $emoji_categories = array();
    
    /**
     * Plugin URL for assets
     * 
     * @var string
     */
    private $plugin_url;
    
    /**
     * Constructor
     */
    public function __construct( $plugin_url = '' ) {
        $this->plugin_url = $plugin_url;
        $this->init_emoji_categories();
    }
    
    /**
     * Initialize emoji categories
     */
    private function init_emoji_categories() {
        $this->emoji_categories = array(
            'smileys' => array(
                'label' => __( 'Smileys & Emotionen', 'psource-chat' ),
                'icon' => '😀',
                'emojis' => array(
                    '😀', '😃', '😄', '😁', '😆', '😅', '🤣', '😂', '🙂', '🙃',
                    '😉', '😊', '😇', '🥰', '😍', '🤩', '😘', '😗', '☺️', '😚',
                    '😙', '🥲', '😋', '😛', '😜', '🤪', '😝', '🤑', '🤗', '🤭',
                    '🤫', '🤔', '🤐', '🤨', '😐', '😑', '😶', '😏', '😒', '🙄',
                    '😬', '🤥', '😔', '😪', '🤤', '😴', '😷', '🤒', '🤕', '🤢',
                    '🤮', '🤧', '🥵', '🥶', '🥴', '😵', '🤯', '🤠', '🥳', '🥸',
                    '😎', '🤓', '🧐', '😕', '😟', '🙁', '☹️', '😮', '😯', '😲',
                    '😳', '🥺', '😦', '😧', '😨', '😰', '😥', '😢', '😭', '😱',
                    '😖', '😣', '😞', '😓', '😩', '😫', '🥱', '😤', '😡', '😠',
                    '🤬', '😈', '👿', '💀', '☠️', '💩', '🤡', '👹', '👺', '👻',
                    '👽', '👾', '🤖', '😺', '😸', '😹', '😻', '😼', '😽', '🙀',
                    '😿', '😾'
                )
            ),
            'people' => array(
                'label' => __( 'Menschen & Körper', 'psource-chat' ),
                'icon' => '👋',
                'emojis' => array(
                    '👋', '🤚', '🖐️', '✋', '🖖', '👌', '🤏', '✌️', '🤞', '🤟',
                    '🤘', '🤙', '👈', '👉', '👆', '🖕', '👇', '☝️', '👍', '👎',
                    '✊', '👊', '🤛', '🤜', '👏', '🙌', '👐', '🤲', '🤝', '🙏',
                    '✍️', '💅', '🤳', '💪', '🦾', '🦿', '🦵', '🦶', '👂', '🦻',
                    '👃', '🧠', '🫀', '🫁', '🦷', '🦴', '👀', '👁️', '👅', '👄',
                    '💋', '🩸', '👶', '🧒', '👦', '👧', '🧑', '👱', '👨', '🧔',
                    '👩', '🧓', '👴', '👵', '🙍', '🙎', '🙅', '🙆', '💁', '🙋',
                    '🧏', '🙇', '🤦', '🤷', '👮', '🕵️', '💂', '🥷', '👷', '🤴',
                    '👸', '👳', '👲', '🧕', '🤵', '👰', '🤰', '🤱', '👼', '🎅',
                    '🤶', '🦸', '🦹', '🧙', '🧚', '🧛', '🧜', '🧝', '🧞', '🧟',
                    '💆', '💇', '🚶', '🧍', '🧎', '🏃', '🕺', '💃', '🕴️', '👯',
                    '🧖', '🧗', '🤺', '🏇', '⛷️', '🏂', '🏌️', '🏄', '🚣', '🏊',
                    '⛹️', '🏋️', '🚴', '🚵', '🤸', '🤼', '🤽', '🤾', '🤹', '🧘'
                )
            ),
            'animals' => array(
                'label' => __( 'Tiere & Natur', 'psource-chat' ),
                'icon' => '🐶',
                'emojis' => array(
                    '🐶', '🐱', '🐭', '🐹', '🐰', '🦊', '🐻', '🐼', '🐻‍❄️', '🐨',
                    '🐯', '🦁', '🐮', '🐷', '🐽', '🐸', '🐵', '🙈', '🙉', '🙊',
                    '🐒', '🐔', '🐧', '🐦', '🐤', '🐣', '🐥', '🦆', '🦅', '🦉',
                    '🦇', '🐺', '🐗', '🐴', '🦄', '🐝', '🐛', '🦋', '🐌', '🐞',
                    '🐜', '🦟', '🦗', '🕷️', '🕸️', '🦂', '🐢', '🐍', '🦎', '🦖',
                    '🦕', '🐙', '🦑', '🦐', '🦞', '🦀', '🐡', '🐠', '🐟', '🐬',
                    '🐳', '🐋', '🦈', '🐊', '🐅', '🐆', '🦓', '🦍', '🦧', '🐘',
                    '🦛', '🦏', '🐪', '🐫', '🦒', '🦘', '🐃', '🐂', '🐄', '🐎',
                    '🐖', '🐏', '🐑', '🦙', '🐐', '🦌', '🐕', '🐩', '🦮', '🐕‍🦺',
                    '🐈', '🐈‍⬛', '🐓', '🦃', '🦚', '🦜', '🦢', '🦩', '🕊️', '🐇',
                    '🦝', '🦨', '🦡', '🦦', '🦥', '🐁', '🐀', '🐿️', '🦔'
                )
            ),
            'food' => array(
                'label' => __( 'Essen & Trinken', 'psource-chat' ),
                'icon' => '🍕',
                'emojis' => array(
                    '🍏', '🍎', '🍐', '🍊', '🍋', '🍌', '🍉', '🍇', '🍓', '🫐',
                    '🍈', '🍒', '🍑', '🥭', '🍍', '🥥', '🥝', '🍅', '🍆', '🥑',
                    '🥦', '🥬', '🥒', '🌶️', '🫑', '🌽', '🥕', '🫒', '🧄', '🧅',
                    '🥔', '🍠', '🥐', '🥯', '🍞', '🥖', '🥨', '🧀', '🥚', '🍳',
                    '🧈', '🥞', '🧇', '🥓', '🥩', '🍗', '🍖', '🦴', '🌭', '🍔',
                    '🍟', '🍕', '🫓', '🥙', '🌮', '🌯', '🫔', '🥗', '🥘', '🫕',
                    '🍝', '🍜', '🍲', '🍛', '🍣', '🍱', '🥟', '🦪', '🍤', '🍙',
                    '🍚', '🍘', '🍥', '🥠', '🥮', '🍢', '🍡', '🍧', '🍨', '🍦',
                    '🥧', '🧁', '🍰', '🎂', '🍮', '🍭', '🍬', '🍫', '🍿', '🍩',
                    '🍪', '🌰', '🥜', '🍯', '🥛', '🍼', '☕', '🫖', '🍵', '🧃',
                    '🥤', '🧋', '🍶', '🍺', '🍻', '🥂', '🍷', '🥃', '🍸', '🍹',
                    '🧊', '🥄', '🍴', '🍽️', '🥢', '🥡'
                )
            ),
            'activities' => array(
                'label' => __( 'Aktivitäten', 'psource-chat' ),
                'icon' => '⚽',
                'emojis' => array(
                    '⚽', '🏀', '🏈', '⚾', '🥎', '🎾', '🏐', '🏉', '🥏', '🎱',
                    '🪀', '🏓', '🏸', '🏒', '🏑', '🥍', '🏏', '🪃', '🥅', '⛳',
                    '🪁', '🏹', '🎣', '🤿', '🥊', '🥋', '🎽', '🛹', '🛷', '⛸️',
                    '🥌', '🎿', '⛷️', '🏂', '🪂', '🏋️‍♀️', '🏋️', '🏋️‍♂️', '🤼‍♀️', '🤼',
                    '🤼‍♂️', '🤸‍♀️', '🤸', '🤸‍♂️', '⛹️‍♀️', '⛹️', '⛹️‍♂️', '🤺', '🤾‍♀️', '🤾',
                    '🤾‍♂️', '🏌️‍♀️', '🏌️', '🏌️‍♂️', '🏇', '🧘‍♀️', '🧘', '🧘‍♂️', '🏄‍♀️', '🏄',
                    '🏄‍♂️', '🏊‍♀️', '🏊', '🏊‍♂️', '🤽‍♀️', '🤽', '🤽‍♂️', '🚣‍♀️', '🚣', '🚣‍♂️',
                    '🧗‍♀️', '🧗', '🧗‍♂️', '🚵‍♀️', '🚵', '🚵‍♂️', '🚴‍♀️', '🚴', '🚴‍♂️', '🏆',
                    '🥇', '🥈', '🥉', '🏅', '🎖️', '🏵️', '🎗️', '🎫', '🎟️', '🎪',
                    '🤹', '🤹‍♀️', '🤹‍♂️', '🎭', '🩰', '🎨', '🎬', '🎤', '🎧', '🎼',
                    '🎵', '🎶', '🥇', '🥈', '🥉', '🏆', '🏅', '🎖️', '🏵️', '🎗️'
                )
            ),
            'travel' => array(
                'label' => __( 'Reisen & Orte', 'psource-chat' ),
                'icon' => '🚗',
                'emojis' => array(
                    '🚗', '🚕', '🚙', '🚌', '🚎', '🏎️', '🚓', '🚑', '🚒', '🚐',
                    '🛻', '🚚', '🚛', '🚜', '🏍️', '🛵', '🚲', '🛴', '🛹', '🛼',
                    '🚁', '✈️', '🛩️', '🛫', '🛬', '🪂', '💺', '🚀', '🛸', '🚉',
                    '🚊', '🚝', '🚞', '🚋', '🚃', '🚋', '🚆', '🚄', '🚅', '🚈',
                    '🚂', '🚖', '🚘', '🚔', '🚍', '🚘', '🚖', '🚡', '🚠', '🚟',
                    '🎢', '🎡', '🎠', '🏗️', '🌁', '🗼', '🏭', '⛲', '🎑', '⛰️',
                    '🏔️', '🗻', '🌋', '🏕️', '🏖️', '🏜️', '🏝️', '🏞️', '🏟️', '🏛️',
                    '🏗️', '🧱', '🪨', '🪵', '🛖', '🏘️', '🏚️', '🏠', '🏡', '🏢',
                    '🏣', '🏤', '🏥', '🏦', '🏨', '🏩', '🏪', '🏫', '🏬', '🏭',
                    '🏯', '🏰', '🗼', '🗽', '⛪', '🕌', '🛕', '🕍', '⛩️', '🕋',
                    '⛺', '🌁', '🌃', '🏙️', '🌄', '🌅', '🌆', '🌇', '🌉', '♨️'
                )
            ),
            'objects' => array(
                'label' => __( 'Objekte', 'psource-chat' ),
                'icon' => '💎',
                'emojis' => array(
                    '⌚', '📱', '📲', '💻', '⌨️', '🖥️', '🖨️', '🖱️', '🖲️', '🕹️',
                    '🗜️', '💽', '💾', '💿', '📀', '📼', '📷', '📸', '📹', '🎥',
                    '📽️', '🎞️', '📞', '☎️', '📟', '📠', '📺', '📻', '🎙️', '🎚️',
                    '🎛️', '🧭', '⏱️', '⏲️', '⏰', '🕰️', '⌛', '⏳', '📡', '🔋',
                    '🔌', '💡', '🔦', '🕯️', '🪔', '🧯', '🛢️', '💸', '💵', '💴',
                    '💶', '💷', '💰', '💳', '💎', '⚖️', '🦯', '🧰', '🔧', '🔨',
                    '⛏️', '🛠️', '⚙️', '🔩', '⚗️', '🧪', '🧫', '🧬', '🔬', '🔭',
                    '📏', '📐', '📌', '📍', '📎', '🖇️', '📏', '📐', '✂️', '🗃️',
                    '🗄️', '🗑️', '🔒', '🔓', '🔏', '🔐', '🔑', '🗝️', '🔨', '🪓',
                    '⛏️', '⚒️', '🛠️', '🗡️', '⚔️', '🔫', '🪃', '🏹', '🛡️', '🪚',
                    '🔧', '🪛', '🔩', '⚙️', '🗜️', '⚖️', '🦯', '🔗', '⛓️', '🪝'
                )
            ),
            'symbols' => array(
                'label' => __( 'Symbole', 'psource-chat' ),
                'icon' => '❤️',
                'emojis' => array(
                    '❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔',
                    '❣️', '💕', '💞', '💓', '💗', '💖', '💘', '💝', '💟', '☮️',
                    '✝️', '☪️', '🕉️', '☸️', '✡️', '🔯', '🕎', '☯️', '☦️', '🛐',
                    '⛎', '♈', '♉', '♊', '♋', '♌', '♍', '♎', '♏', '♐',
                    '♑', '♒', '♓', '🆔', '⚛️', '🉑', '☢️', '☣️', '📴', '📳',
                    '🈶', '🈚', '🈸', '🈺', '🈷️', '✴️', '🆚', '💮', '🉐', '㊙️',
                    '㊗️', '🈴', '🈵', '🈹', '🈲', '🅰️', '🅱️', '🆎', '🆑', '🅾️',
                    '🆘', '❌', '⭕', '🛑', '⛔', '📛', '🚫', '💯', '💢', '♨️',
                    '🚷', '🚯', '🚳', '🚱', '🔞', '📵', '🚭', '❗', '❕', '❓',
                    '❔', '‼️', '⁉️', '🔅', '🔆', '〽️', '⚠️', '🚸', '🔱', '⚜️',
                    '🔰', '♻️', '✅', '🈯', '💹', '❇️', '✳️', '❎', '🌐', '💠'
                )
            ),
            'flags' => array(
                'label' => __( 'Flaggen', 'psource-chat' ),
                'icon' => '🏁',
                'emojis' => array(
                    '🏁', '🚩', '🎌', '🏴', '🏳️', '🏳️‍🌈', '🏳️‍⚧️', '🏴‍☠️', '🇦🇫', '🇦🇽',
                    '🇦🇱', '🇩🇿', '🇦🇸', '🇦🇩', '🇦🇴', '🇦🇮', '🇦🇶', '🇦🇬', '🇦🇷', '🇦🇲',
                    '🇦🇼', '🇦🇺', '🇦🇹', '🇦🇿', '🇧🇸', '🇧🇭', '🇧🇩', '🇧🇧', '🇧🇾', '🇧🇪',
                    '🇧🇿', '🇧🇯', '🇧🇲', '🇧🇹', '🇧🇴', '🇧🇦', '🇧🇼', '🇧🇷', '🇮🇴', '🇻🇬',
                    '🇧🇳', '🇧🇬', '🇧🇫', '🇧🇮', '🇰🇭', '🇨🇲', '🇨🇦', '🇮🇨', '🇨🇻', '🇧🇶',
                    '🇰🇾', '🇨🇫', '🇹🇩', '🇨🇱', '🇨🇳', '🇨🇽', '🇨🇨', '🇨🇴', '🇰🇲', '🇨🇬',
                    '🇨🇩', '🇨🇰', '🇨🇷', '🇨🇮', '🇭🇷', '🇨🇺', '🇨🇼', '🇨🇾', '🇨🇿', '🇩🇰',
                    '🇩🇯', '🇩🇲', '🇩🇴', '🇪🇨', '🇪🇬', '🇸🇻', '🇬🇶', '🇪🇷', '🇪🇪', '🇸🇿',
                    '🇪🇹', '🇪🇺', '🇫🇰', '🇫🇴', '🇫🇯', '🇫🇮', '🇫🇷', '🇬🇫', '🇵🇫', '🇹🇫',
                    '🇬🇦', '🇬🇲', '🇬🇪', '🇩🇪', '🇬🇭', '🇬🇮', '🇬🇷', '🇬🇱', '🇬🇩', '🇬🇵'
                )
            )
        );
        
        // Allow filtering of emoji categories
        $this->emoji_categories = apply_filters( 'psource_chat_emoji_categories', $this->emoji_categories );
    }
    
    /**
     * Get all emoji categories
     * 
     * @return array
     */
    public function get_categories() {
        return $this->emoji_categories;
    }
    
    /**
     * Get emojis for a specific category
     * 
     * @param string $category_key
     * @return array|false
     */
    public function get_category_emojis( $category_key ) {
        if ( isset( $this->emoji_categories[ $category_key ] ) ) {
            return $this->emoji_categories[ $category_key ]['emojis'];
        }
        return false;
    }
    
    /**
     * Get all emojis as a flat array
     * 
     * @return array
     */
    public function get_all_emojis() {
        $all_emojis = array();
        foreach ( $this->emoji_categories as $category ) {
            $all_emojis = array_merge( $all_emojis, $category['emojis'] );
        }
        return $all_emojis;
    }
    
    /**
     * Generate the modern emoji picker HTML
     * 
     * @param array $chat_session
     * @return string
     */
    public function generate_emoji_picker( $chat_session = array() ) {
        return $this->generate_emoji_button();
    }
    
    /**
     * Generate only the emoji button (for use in the menu list)
     * 
     * @return string
     */
    public function generate_emoji_button() {
        $categories = $this->get_categories();
        
        if ( empty( $categories ) ) {
            return '';
        }
        
        // Get first emoji for the button
        $first_category = reset( $categories );
        $first_emoji = isset( $first_category['emojis'][0] ) ? $first_category['emojis'][0] : '😀';
        
        // Only the button inside the list
        $content = '<li class="psource-chat-send-input-emoticons">';
        $content .= '<a class="psource-chat-emoticons-menu" href="#" title="' . __( 'Emoji auswählen', 'psource-chat' ) . '">';
        $content .= '<span class="psource-chat-emoji-trigger">' . $first_emoji . '</span>';
        $content .= '</a>';
        $content .= '</li>';
        
        return $content;
    }
    
    /**
     * Generate emoji picker modal HTML
     * 
     * @return string
     */
    public function generate_emoji_picker_modal() {
        $categories = $this->get_categories();
        
        if ( empty( $categories ) ) {
            return '';
        }
        
        $content = '';
        
        // Modern emoji picker container - positioned absolutely over chatbox
        $content .= '<div class="psource-chat-emoji-picker">';
        $content .= '<div>'; // Inner container for flexbox centering
        
        // Suchfeld und Schließen-Button
        $content .= '<div class="psource-chat-emoji-header">';
        $content .= '<input type="text" class="psource-chat-emoji-search" placeholder="' . __( 'Emoji suchen...', 'psource-chat' ) . '" autocomplete="off" />';
        $content .= '<button class="psource-chat-emoji-close" type="button" title="' . __( 'Schließen', 'psource-chat' ) . '">×</button>';
        $content .= '</div>';
        
        // Category tabs
        $content .= '<div class="psource-chat-emoji-categories">';
        $is_first = true;
        foreach ( $categories as $key => $category ) {
            $active_class = $is_first ? ' active' : '';
            $content .= '<button type="button" class="psource-chat-emoji-category-tab' . $active_class . '" data-category="' . esc_attr( $key ) . '" title="' . esc_attr( $category['label'] ) . '">';
            $content .= '<span class="psource-chat-emoji-category-icon">' . $category['icon'] . '</span>';
            $content .= '</button>';
            $is_first = false;
        }
        $content .= '</div>';
        
        // Emoji grid container
        $content .= '<div class="psource-chat-emoji-grid-container">';
        $is_first = true;
        foreach ( $categories as $key => $category ) {
            $active_class = $is_first ? ' active' : '';
            $content .= '<div class="psource-chat-emoji-grid' . $active_class . '" data-category="' . esc_attr( $key ) . '">';
            
            foreach ( $category['emojis'] as $emoji ) {
                $content .= '<button type="button" class="psource-chat-emoji-item" data-emoji="' . esc_attr( $emoji ) . '" data-label="' . esc_attr( $category['label'] ) . '" title="' . esc_attr( $emoji ) . '">';
                $content .= $emoji;
                $content .= '</button>';
            }
            
            $content .= '</div>';
            $is_first = false;
        }
        $content .= '</div>'; // .psource-chat-emoji-grid-container
        
        $content .= '</div>'; // Inner container
        $content .= '</div>'; // .psource-chat-emoji-picker
        
        return $content;
    }
    
    /**
     * Get emoji picker styles
     * 
     * @return string
     */
    public function get_emoji_picker_styles() {
        return "
        /* Modern Emoji Picker Styles - Modal Overlay */
        .psource-chat-emoji-picker {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            display: none;
            z-index: 10000;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            align-items: center;
            justify-content: center;
            padding: 0px;
            box-sizing: border-box;
        }
        
        .psource-chat-emoji-picker.active {
            display: flex !important;
        }
        
        .psource-chat-emoji-picker > div {
            position: relative;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            width: calc(100% - 20px);
            height: calc(100% - 20px);
            max-width: none;
            max-height: none;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-sizing: border-box;
            margin: 10px;
        }
        
        .psource-chat-emoji-header {
            padding: 12px;
            border-bottom: 1px solid #e1e1e1;
            display: flex;
            gap: 6px;
            align-items: center;
            flex-shrink: 0;
        }
        
        .psource-chat-emoji-search {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s ease;
            box-sizing: border-box;
            max-width: 80%;
        }
        
        .psource-chat-emoji-search:focus {
            border-color: #007cba;
        }
        
        .psource-chat-emoji-close {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            padding: 4px;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: all 0.2s ease;
            color: #dc3545;
            flex-shrink: 0;
            font-weight: bold;
        }
        
        .psource-chat-emoji-close:hover {
            background: #ffe0e6;
            color: #a02830;
            transform: scale(1.1);
        }
        
        .psource-chat-emoji-categories {
            display: flex;
            border-bottom: 1px solid #e1e1e1;
            background: #f8f9fa;
            padding: 4px;
            gap: 2px;
            flex-shrink: 0;
            overflow-x: auto;
        }
        
        .psource-chat-emoji-category-tab {
            flex-shrink: 0;
            background: none;
            border: none;
            padding: 6px 8px;
            cursor: pointer;
            border-radius: 4px;
            transition: all 0.2s ease;
            font-size: 16px;
            line-height: 1;
            min-width: 32px;
            text-align: center;
        }
        
        .psource-chat-emoji-category-tab:hover {
            background: #e9ecef;
        }
        
        .psource-chat-emoji-category-tab.active {
            background: #007cba;
            color: white;
        }
        
        .psource-chat-emoji-category-icon {
            display: block;
        }
        
        .psource-chat-emoji-grid-container {
            flex: 1;
            overflow-y: auto;
            position: relative;
            padding: 6px;
            box-sizing: border-box;
        }
        
        .psource-chat-emoji-grid {
            display: none;
            grid-template-columns: repeat(auto-fit, minmax(32px, 1fr));
            gap: 2px;
        }
        
        .psource-chat-emoji-grid.active {
            display: grid;
        }
        
        .psource-chat-emoji-item {
            background: none;
            border: none;
            padding: 2px;
            cursor: pointer;
            border-radius: 3px;
            font-size: clamp(16px, 5vw, 22px);
            line-height: 1;
            transition: all 0.2s ease;
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 32px;
        }
        
        .psource-chat-emoji-item:hover {
            background: #f0f0f0;
            transform: scale(1.1);
        }
        
        .psource-chat-emoji-item:active {
            transform: scale(0.95);
        }
        
        .psource-chat-emoji-grid-container::-webkit-scrollbar {
            width: 8px;
        }
        
        .psource-chat-emoji-grid-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        .psource-chat-emoji-grid-container::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        
        .psource-chat-emoji-grid-container::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        .psource-chat-emoji-trigger {
            font-size: 16px;
            line-height: 1;
        }
        
        .psource-chat-send-input-emoticons {
            position: relative;
        }
        ";
    }
    
    /**
     * Get emoji picker JavaScript
     * 
     * @return string
     */
    public function get_emoji_picker_script() {
        return "
        // Modern Emoji Picker JavaScript
        (function($) {
            'use strict';
            
            // Emoji picker functionality
            function initEmojiPicker() {
                // Toggle emoji picker
                $(document).on('click', '.psource-chat-emoticons-menu', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    var picker = $(this).siblings('.psource-chat-emoji-picker');
                    var wasVisible = picker.hasClass('active');
                    
                    // Close all other emoji pickers
                    $('.psource-chat-emoji-picker').removeClass('active');
                    
                    // Toggle current picker
                    if (!wasVisible) {
                        picker.addClass('active');
                    }
                });
                
                // Category tab switching
                $(document).on('click', '.psource-chat-emoji-category-tab', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    var category = $(this).data('category');
                    var picker = $(this).closest('.psource-chat-emoji-picker');
                    
                    // Update active tab
                    picker.find('.psource-chat-emoji-category-tab').removeClass('active');
                    $(this).addClass('active');
                    
                    // Update active grid
                    picker.find('.psource-chat-emoji-grid').removeClass('active');
                    picker.find('.psource-chat-emoji-grid[data-category=\"' + category + '\"]').addClass('active');
                });
                
                // Emoji selection
                $(document).on('click', '.psource-chat-emoji-item', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    var emoji = $(this).data('emoji');
                    
                    // Find the textarea within the same chat module
                    var chatModule = $(this).closest('.psource-chat-module-message-area');
                    var textarea = chatModule.find('textarea.psource-chat-send');
                    
                    if (textarea.length) {
                        // Insert emoji at cursor position
                        var currentText = textarea.val();
                        var cursorPos = textarea[0].selectionStart || currentText.length;
                        var newText = currentText.slice(0, cursorPos) + emoji + currentText.slice(cursorPos);
                        
                        textarea.val(newText);
                        
                        // Set cursor position after emoji
                        var newCursorPos = cursorPos + emoji.length;
                        if (textarea[0].setSelectionRange) {
                            textarea[0].setSelectionRange(newCursorPos, newCursorPos);
                        }
                        
                        // Focus textarea
                        textarea.focus();
                    } else {
                        // Fallback: try to find any visible textarea in the chat
                        var fallbackTextarea = $('textarea.psource-chat-send:visible').first();
                        if (fallbackTextarea.length) {
                            var currentText = fallbackTextarea.val();
                            var cursorPos = fallbackTextarea[0].selectionStart || currentText.length;
                            var newText = currentText.slice(0, cursorPos) + emoji + currentText.slice(cursorPos);
                            
                            fallbackTextarea.val(newText);
                            
                            var newCursorPos = cursorPos + emoji.length;
                            if (fallbackTextarea[0].setSelectionRange) {
                                fallbackTextarea[0].setSelectionRange(newCursorPos, newCursorPos);
                            }
                            
                            fallbackTextarea.focus();
                        }
                    }
                    
                    // Close emoji picker
                    $(this).closest('.psource-chat-emoji-picker').removeClass('active');
                });
                
                // Close emoji picker when clicking outside
                $(document).on('click', function(e) {
                    if (!$(e.target).closest('.psource-chat-send-input-emoticons').length) {
                        $('.psource-chat-emoji-picker').removeClass('active');
                    }
                });
                
                // Prevent emoji picker from closing when clicking inside
                $(document).on('click', '.psource-chat-emoji-picker', function(e) {
                    e.stopPropagation();
                });
            }
            
            // Initialize when document is ready
            $(document).ready(function() {
                initEmojiPicker();
            });
            
            // Re-initialize on AJAX updates
            $(document).on('psource_chat_content_updated', function() {
                initEmojiPicker();
            });
            
        })(jQuery);
        ";
    }
}

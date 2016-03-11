<?php

class WPDM_Messages {
    public static function Message($msg, $die = 0){
        $msg = "<div class='w3eden'><div class='alert alert-{$msg['type']}'><div class='media'><div class='pull-left'><i class='fa fa-2x fa-{$msg['icon']}'></i></div><div class='media-body'><b>{$msg['title']}</b><br/>{$msg['message']}</div></div></div>";
        if($die==-1) return $msg;
        echo $msg;
        if($die==1) die();
        return true;
    }

    public static function Error($msg, $die = 0){
        if(is_string($msg)) $msg['message'] = $msg;
        if(!isset($msg['title'])) $msg['title'] = 'Error!';
        $msg['type'] = 'danger';
        $msg['icon'] = 'exclamation-triangle';
        return self::Message($msg, $die);
    }

    public static function Warning($msg, $die = 0){
        if(is_string($msg)) $msg['message'] = $msg;
        if(!isset($msg['title'])) $msg['title'] = 'Warning!';
        $msg['type'] = 'warning';
        $msg['icon'] = 'exclamation-circle';
        return self::Message($msg, $die);
    }

    public static function Info($msg, $die = 0){
        if(is_string($msg)) $msg['message'] = $msg;
        if(!isset($msg['title'])) $msg['title'] = 'Attention!';
        $msg['type'] = 'info';
        $msg['icon'] = 'info-circle';
        return self::Message($msg, $die);
    }

    public static function Success($msg, $die = 0){
        if(is_string($msg)) $msg['message'] = $msg;
        if(!isset($msg['title'])) $msg['title'] = 'Awesome!';
        $msg['type'] = 'success';
        $msg['icon'] = 'check-circle';
        return self::Message($msg, $die);
    }
} 
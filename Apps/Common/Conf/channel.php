<?php
$cacheName = 'WEB_CHANNEL_CACHE';
$tmp = S($cacheName);
$channelData = [];
if ($tmp == false) {
    $channelData = M('channel')->field('name,url,color,icon,is_new,target,sid')->where(['status' => 1])->order('sort asc')->select();
    S($cacheName, serialize($channelData));
} else {
    $channelData = unserialize($tmp);
    unset($tmp);
}

return [
    'WEB_CHANNEL' => $channelData,   
];
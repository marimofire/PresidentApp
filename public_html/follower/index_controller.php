<?php
/**
 * Followerリストを表示する画面
 */
require_once dirname(__FILE__) . '/../facebook.php';
require_once dirname(__FILE__) . '/../model/Follower.php';
require_once dirname(__FILE__) . '/../model/Party.php';

// Follower,Party Model用意
$Follower = new Follower();
$Party = new Party();

// 全Follower情報取得を試みる
$fql = 'SELECT uid,name,pic,sex FROM user WHERE uid IN (SELECT uid2 FROM friend WHERE uid1 = me())';
$followers = $facebook->api(array('method' => 'fql.query', 'query' => $fql));
$followers_data = array();

// 全Followerの情報がMySQLにあるか確認　なければINSERT
foreach ( $followers as $follower ) {
  $uid = $follower['uid'];
  $result = $Follower->findBy(array('facebook_id' => $uid));
  if ( $result->num_rows == 0 ) {
    // INSERTするデータを用意
    $data = array(
      'facebook_id' => $uid,
      'name' => $follower['name'],
      'power' => Job::getPower(),
      'money' => Job::getMoney(),
      'pic' => $follower['pic'],
      'sex' => $follower['sex'] == 'male' ? 0 : 1,
      'job_name' => Job::getJob()
    );
    $Follower->insert($data);
    array_push($followers_data, $data);
  } else {
    $follower_info = $result->fetch_assoc();
    array_push($followers_data, $follower_info );
  }
}

$party = array('', '', '');
$result = $Party->findBy(array('president_id' => $facebook->getUser()));
$index = 0;
while ( $p = $result->fetch_assoc() ) {
  $party[$index++] = $p;
}
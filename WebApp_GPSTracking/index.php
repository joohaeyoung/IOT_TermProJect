<?php include "header.php" ?>

<?php
$host = "mysql:host=localhost;dbname=jhy753";
$user = "jhy753";
$password= "1q2w3e4r5t";
$conn = new PDO($host, $user, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));;

$stmt = $conn->prepare('SELECT * FROM gps');
$stmt->execute();
$list = $stmt->fetchAll();

foreach($list as $gps){
  $latitude = $gps['latitude'];
  $longitude = $gps['longitude'];
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>    
  <meta charset="UTF-8">
  <style>
    #map{
      margin:15px auto;
    }
  </style>
  <script type="text/javascript" src="//apis.daum.net/maps/maps3.js?apikey=32f7f180ebb29650479304dea55b573c"></script>
    <script
    src="https://code.jquery.com/jquery-1.12.4.min.js"
    integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="
    crossorigin="anonymous">
  </script>
  <script language="JavaScript">
  // 전역 변수 선언
    var map = null;
    var marker = null;
    var latitude = <?=$latitude?>;
    var longitude = <?=$longitude?>;

    $(document).ready(function(){
      init();
      // 3초마다 moveMap() 메소드를 반복
      setInterval(moveMap, 3000);
    });

    // 최초 1회 실행
    function init() {
      // 위치 정보 옵션
      var container = document.getElementById('map');
      var options = {
        center: new daum.maps.LatLng(latitude, longitude),
        level: 3
      };
      // 맵을 그린다
      map = new daum.maps.Map(container, options);
      // 최초 좌표
      var firstPos  = new daum.maps.LatLng(latitude, longitude);
      // 마커 생성
      marker = new daum.maps.Marker({
        position: firstPos
      });
      // 마커가 지도 위에 표시되도록 설정합니다
      marker.setMap(map);
    }
    // 반복되는 메소드
    function moveMap(){
      $.ajax({
        // 조회하는 코드가 들어갈 부분
        url:'./getGPS.php',
        dataType:'json',
        // 성공하면
        success:function(data){
          // 이전 좌표가 사라진다.
          marker.setMap(null);
          // 새로운 좌표
          latitude = data.latitude;
          longitude = data.longitude;
          var myLatlng = new daum.maps.LatLng(latitude, longitude);
          console.log(data);
          console.log(latitude);
          console.log(longitude);
          // 새로운 마커
          marker = new daum.maps.Marker({
            position: myLatlng
          });
          // 마커 추가
          marker.setMap(map);
          // 맵 중심 이동
          map.panTo(new daum.maps.LatLng(latitude, longitude));
      }
    })
  }
  </script>
</head>
<body>
    <div id="map" style="width:90%; height:1000px;"></div>
</body>
 
</html>

<?php include "footer.php" ?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title></title>
        <script language="javascript" src="https://apis.skplanetx.com/tmap/js?version=1&format=javascript&appKey=78a73856-9c9b-35d3-9ffb-8e90e577da5d"></script>   
        <script type="text/javascript" src="jquery-1.8.0.js"></script>
        <script type="text/javascript" src="planetxsdk.js"></script>
        <script>
        
        <?php
            $host = "localhost";          // MySQL DB서버 host

            $user = "root";                  // MySQL DB서버 접속 id

            $password = "apmsetup";  // MySQL DB서버 접속 password

            $dbname = "testdb";       // MySQL DB명

            $dbconn = mysqli_connect($host, $user, $password, $dbname);

            if(mysqli_connect_errno($dbconn)){
                echo "데이터베이스 연결 실패:";
            }
        ?>

        <?php
            
            $sql1 = "SELECT * FROM gps ORDER BY no DESC limit 1";
            //핸드폰 gps
            $sql2 = "SELECT * FROM get_point ORDER BY no DESC limit 1";
            //경로좌표 gps
            $result1 = mysqli_query($dbconn, $sql1);
            $result2 = mysqli_query($dbconn, $sql2);

            $row1 = mysqli_fetch_object($result1);
            $row2 = mysqli_fetch_object($result2);

        ?>
            var lng1 = <?=$row1->longitude?>;
            var lat1 = <?=$row1->latitude?>;
            //핸드폰 gps
            var lng2 = <?=$row2->longitude?>;
            var lat2 = <?=$row2->latitude?>;
            //경로좌표 gps
          
            //좌표를 tmap형식으로 변환
            var pr_3857 = new Tmap.Projection("EPSG:3857"); //14101697.1565, 4293051.6553 (경도, 위도)
            var pr_4326 = new Tmap.Projection("EPSG:4326"); // wgs84   126.6765, 35.9478 (경도, 위도)
        
            var lonlat1 = new Tmap.LonLat(lng1, lat1);
            var lonlat2 = new Tmap.LonLat(lng2, lat2).transform(pr_3857, pr_4326);  
        //변환된 두 좌표 사이의 거리 구하기
        function getDistanceFromLatLonInKm(lonlat1, lonlat2) {
            function deg2rad(deg) {
                return deg * (Math.PI/180)
            }
            
            //현재 좌표 = > 원하는 좌표
            var R = 6371; // Radius of the earth in km
            var dLat = deg2rad(lonlat2.lat-lonlat1.lat);  // deg2rad below
            var dLon = deg2rad(lonlat2.lon-lonlat1.lon);
            var a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(deg2rad(lonlat1.lat)) * Math.cos(deg2rad(lonlat2.lat-lonlat1.lat)) * Math.sin(dLon/2) * Math.sin(dLon/2);
            var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            var d = R * c; // Distance in km
            document.write(d+"km");
         
        }

        //두 좌표의 방향각 구하기
        function bearingP1toP2(lonlat1, lonlat2){
             

            var Cur_Lat_radian = lonlat1.lat * (3.141592 / 180);
            var Cur_Lon_radian = lonlat1.lon * (3.141592 / 180);

            var Dest_Lat_radian = lonlat2.lat * (3.141592 / 180);
            var Dest_Lon_radian = lonlat2.lon * (3.141592 / 180);

            var radian_distance = 0;

            radian_distance = Math.acos(Math.sin(Cur_Lat_radian) * Math.sin(Dest_Lat_radian) + Math.cos(Cur_Lat_radian) * Math.cos(Dest_Lat_radian) * Math.cos(Cur_Lon_radian - Dest_Lon_radian));

            var radian_bearing = Math.acos((Math.sin(Dest_Lat_radian) - Math.sin(Cur_Lat_radian) * Math.cos(radian_distance)) / (Math.cos(Cur_Lat_radian) * Math.sin(radian_distance)));        // acos의 인수로 주어지는 x는 360분법의 각도가 아닌 radian(호도)값이다.

            var true_bearing = 0;

            if (Math.sin(Dest_Lon_radian - Cur_Lon_radian) < 0){
                true_bearing = radian_bearing * (180 / 3.141592);
                true_bearing = 360 - true_bearing;
            }
            else{
                true_bearing = radian_bearing * (180 / 3.141592);
            }
            document.write(true_bearing);

        }




        //getDistanceFromLatLonInKm(lonlat1, lonlat2);
        bearingP1toP2(lonlat1, lonlat2)
        //2개의 함수 실행
</script>
</head>
    <body>
    
       
    </body>
</html>
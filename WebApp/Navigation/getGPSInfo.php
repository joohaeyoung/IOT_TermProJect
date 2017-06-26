<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Map_koreatech</title>
        <script language="javascript" src="https://apis.skplanetx.com/tmap/js?version=1&format=javascript&appKey=78a73856-9c9b-35d3-9ffb-8e90e577da5d"></script>   
        <script type="text/javascript" src="jquery-1.8.0.js"></script>
        <script type="text/javascript" src="planetxsdk.js"></script>

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
            $lon;
            $lat;
            $sql = "SELECT * FROM gps ORDER BY no DESC limit 1";
            $sql_insert = "http://localhost/DKE/dbconfig.php?longitude="+$lon+"&latitude= "+$lat;

            $result = mysqli_query($dbconn, $sql);
            $row = mysqli_fetch_object($result);


        ?>


        <div id = "menu1" style="text-decoration:none;">
        
        <br/>
        <div>&nbsp;<a style="text-decoration:none;" href="javascript:selectMenu('1')"><font color="black">&nbsp;>>현재 위치 주변 poi 검색</font></a></div>
        <br/>
        <input type="text" NAME = "searchText1" id = "searchText1" VALUE="" SIZE=15 MAXLENGTH=15>
        <input type="button" NAME = "button1" id = "button1" VALUE="검색" onclick="searchPOI()" SIZE=12 MAXLENGTH=12 >
        <input type="button" NAME = "button2" id = "button2" VALUE="리셋" onclick="reload()" SIZE=12 MAXLENGTH=12 >

        
        <br/>
        <br/>
        </div>
        
        <script>
        function reload() { 
            document.location.reload();     
        }
        //화면 초기화 기능
            //모바일에서 눌렀을 때 touchstart
         function initTmap(){
            
            map = new Tmap.Map({div:'map_div',
                        width:'80%', 
                        height:'400px',
                        animation:false
                        });
            map.setCenter(new Tmap.LonLat(14168799.15603861,4405875.90357642));
            map.addControls([
                    new Tmap.Control.KeyboardDefaults(),//상하좌우로 키보드사용한다.
                    new Tmap.Control.MousePosition(),//현재 커서의 좌표값을 쵸시해준다.
                    new Tmap.Control.OverviewMap()//미니맵을 하나더 보여준다.
                    ]);
            

            addMarkerLayer();                   
            //기본 맵 실행창, 초기 화면 설정

            
        };


        function addMarkerLayer(){
            markerLayer = new Tmap.Layer.Markers("marker");
            map.addLayer(markerLayer);
        };
        //입력된 정보에 대한 마커를 생성.

        function addMarker(options){

            var size = new Tmap.Size(12,19);
            var offset = new Tmap.Pixel(-(size.w/2), -size.h);
            var icon = new Tmap.Icon("https://developers.skplanetx.com/upload/tmap/marker/pin_b_s_simple.png",size,offset);
            var marker = new Tmap.Markers(options.lonlat,icon,options.label);

            markerLayer.addMarker(marker);
            marker.events.register("mouseover", marker, onOverMouse);
            marker.events.register("mouseout", marker, onOutMouse);

            map.events.register("click", marker, function(e){

            var opx = map.getLayerPxFromViewPortPx(e.xy) ;
            var lonlat = map.getLonLatFromPixel(opx);
            var searchText1 = jQuery("#searchText1").val();
            
            searchRoute(lonlat, searchText1 );
        });
            
            //마커의 크기및 이미지, 이벤트 설정.
        };
        

        function onOverMouse(e){
            this.popup.show();
        };

        function onOutMouse(e){
            this.popup.hide();
        };

        function searchPOI(){
            clear_marker();
            tdata = new Tmap.TData();
            tdata.events.register("onComplete", tdata, onCompleteTData);
            var center = map.getCenter();
            var searchText = jQuery("#searchText1").val();
            tdata.getPOIDataFromSearch(encodeURIComponent(searchText), {centerLon:center.lon, centerLat:center.lat});

            };
            //입력창의 내용을 저장하고 이를 통해 검색을 실행.
        function clear_marker(){
            if(markerLayer){
                markerLayer.clearMarkers();     
            }       

        }
        //마커 삭제기능.
       //초기화 함수
       
    //경로 정보 로드
    function searchRoute(lonlat, searchText1){
        
        var latitude = <?=$row->latitude?>;
        var longitude = <?=$row->longitude?>;
        
        var pr_3857 = new Tmap.Projection("EPSG:3857"); //14101697.1565, 4293051.6553 (경도, 위도)
        var pr_4326 = new Tmap.Projection("EPSG:4326"); // wgs84   126.6765, 35.9478 (경도, 위도)
        
        var lonlat_gps = new Tmap.LonLat(longitude, latitude).transform(pr_4326, pr_3857); 
        //현재 좌표 = > 원하는 좌표
        //console.log(lonlat_gps.lon);
        //console.log(lonlat_gps.lat);

        var startX = lonlat_gps.lon;
        var startY = lonlat_gps.lat;
        var endX = lonlat.lon;
        var endY = lonlat.lat;
        var startName = "명동";
        var endName = searchText1;
        var urlStr = "https://apis.skplanetx.com/tmap/routes/pedestrian?version=1&format=xml";
            urlStr += "&startX="+startX;
            urlStr += "&startY="+startY;
            urlStr += "&endX="+endX;
            urlStr += "&endY="+endY;
            urlStr += "&startName="+encodeURIComponent(startName);
            urlStr += "&endName="+encodeURIComponent(endName);
            urlStr += "&appKey=78a73856-9c9b-35d3-9ffb-8e90e577da5d";

        var routeFormat = new Tmap.Format.KML({extractStyles:true, extractAttributes:true});

        var prtcl = new Tmap.Protocol.HTTP({
                                        url: urlStr,
                                        format:routeFormat
                                        });

        var routeLayer = new Tmap.Layer.Vector("route", {protocol:prtcl, strategies:[new Tmap.Strategy.Fixed()]});
        
        routeLayer.events.register("featuresadded", routeLayer, onDrawnFeatures);

        map.addLayer(routeLayer);

        
        point_lonlat(startX, startY, endX, endY, startName, endName);

        
    }
    //jsom 객체를 이용한 좌표 데이터 파싱
    function point_lonlat(stx, sty, ex, ey, stn, enn){
        //JSON: 웹서버에서 클라이언트에게 대량의 데이터를 전달하고 할 때 쓰는 포멧 (속성 : 값)의 형태로 이어진다.
        var url = 'https://apis.skplanetx.com/tmap/routes/pedestrian?version=1&callback={callback}';
        //접속하고자 하는 url주소값을 저장한다.
        var params = {
                startX : stx
                ,startY : sty
                ,endX : ex
                ,endY : ey
                ,startName : stn
                ,endName : enn
        }
        //
        $.ajax({
            //비동기식 자바스크립트xml의 약자jquery 메서드중 하나, 화면을 이동하지않고 서버에서 데이터만 가져올수 있는 메서드.
            method: 'POST',
            url: url,
            data: params,
            beforeSend : function(xhr){
                xhr.setRequestHeader("appKey", "78a73856-9c9b-35d3-9ffb-8e90e577da5d");
                xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");
                //헤더파일을 생성하고 해당 url에 접속한 다음 서비스를 사용하기 위한 로그인 인증 과정을 거친다.
        },
            complete: function(data) {
            //해당 인증이 완료 될 경우 결과 값을 가져오도록한다.
            console.log(data.responseText);

            

            
            var array = [];
            var point = [];
            var point2;
            var point3;
            var j = 0;
            var k = 0;
            array = data.responseText;
            //console.log(array);
            //console.log(array.length);
            
            
            for(var i = 0; i < array.length; i++){
                if(array[i]=="[" && array[i+1] == "1"){
                    //array.slice(i+1, i+16);
                    //document.write(array.slice(i+1, i+17)+"   ");
                    i++;
                    for(k = 0; k < 16; k++){
                        point[j++] = array[i++];
                        //document.write(point[j-1]);
                }
                    }
            }
            //document.write(point.length);
            var point_lon = [];//x
            var point_lat = [];//y
            
            for(i = 0; i < point.length/16; i++){
                point2 = point[0 + i*16] + point[1 + i*16] + point[2 + i*16] + point[3 + i*16] + point[4 + i*16] + point[5 + i*16] + point[6 + i*16] + point[7 + i*16];
                //document.write(point2);
                //document.write("    ");
                point3 = point[9 + i*16] + point[10 + i*16] + point[11 + i*16] + point[12 + i*16] + point[13 + i*16] + point[14 + i*16] + point[15 + i*16];
                point_lon[i] = parseFloat(point2);
                point_lat[i] = parseFloat(point3);
                

            }
           
            for(i = 0; i < point.length/16; i++){
                
                //console.log(point_lon[i]+"     ");
                //console.log(point_lat[i]+"     ");
                
            }
           
            
            
            var point_longitude = [];
            var point_latitude = [];
            var q = 0;
            var p = 0;

            for(i=0; i<point_lon.length; i++) 
            { 
                for(k=0; k<i; k++) 
                { 
                    // 중복수가 있으면 중지. 
                    if(point_lon[i] == point_lon[k]) 
                        break; 
                } 
            // 중복수가 없었다면 b배열에 담기. 
                if(i==k) 
                { 
                    point_longitude[q++] = point_lon[i];
                    point_latitude[p++] = point_lat[i];
                }  
            } 
             for(i=0; i<q; i++) 
            { 
                console.log("위도 : "+ point_longitude[i]+"     ");
                console.log("경도 : "+ point_latitude[i]+"     ");
            } 
            
            
    }

        });
    }
    //경로 그리기 후 해당영역으로 줌
    function onDrawnFeatures(e){
        map.zoomToExtent(this.getDataExtent());
    }

        function onCompleteTData(e){
        if(jQuery(this.responseXML).find("searchPoiInfo pois poi").text() != ''){
            jQuery(this.responseXML).find("searchPoiInfo pois poi").each(function(){
            var name = jQuery(this).find("name").text();
            var lon = jQuery(this).find("frontLon").text();
            var lat = jQuery(this).find("frontLat").text();
            var options = {
                label:new Tmap.Label(name),
                lonlat:new Tmap.LonLat(lon, lat)
            };
                addMarker(options);
        });
        }else {
            alert('검색결과가 없습니다.');
        }
            map.zoomToExtent(markerLayer.getDataExtent());
        };

       
           //스크립트 언어를 사용하여 화면에 맵을 출력한다.
           //실질적인 화면의 출력은 아래의 body에서 이뤄진다.
        function onDrawnFeatures(e){
            map.zoomToExtent(this.getDataExtent());
            map.getLayerPxFromViewPortPx
        }
        </script>
       
        

        
    </head>
    <body onload="initTmap()">
    <form name = "mapping">
        <div id="map_div">
        </div>    
    </form>    
    </body>
</html>
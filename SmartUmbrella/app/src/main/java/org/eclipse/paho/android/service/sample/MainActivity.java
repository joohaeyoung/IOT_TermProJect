package org.eclipse.paho.android.service.sample;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.location.Location;
import android.location.LocationListener;
import android.location.LocationManager;
import android.os.Bundle;
import android.util.Log;
import android.view.Menu;
import android.view.MenuItem;
import android.widget.TextView;

import org.eclipse.paho.client.mqttv3.MqttConnectOptions;
import org.eclipse.paho.client.mqttv3.MqttException;
import org.eclipse.paho.client.mqttv3.MqttSecurityException;

import java.util.ArrayList;

import static org.eclipse.paho.android.service.sample.values.clientHandle;

/**
 * Created by hae young Joo on 2017-06-23.
 */

public class MainActivity extends Activity {

    TextView textView1;
    WifiApManager wifiApManager;

    boolean preFlag;
    //ClientScanResult isReachableFlag = new ClientScanResult();


    public static Context mContext;


    //해용 코드
    // MqttAndroidClient client;
    LocationListener locationListener;
    LocationManager locationManager;
    LocationManager lm;


    class PublishThread implements Runnable{

        private boolean stopped = false;
        @Override
        public void run() {
            try{
                Thread.sleep(10000); //2초씩 쉰다.

            }catch (Exception e) {}

            while(!stopped){

                try{
                    Thread.sleep(1000); //2초씩 쉰다.
                }catch (Exception e) {}

                publishGps();
                Log.d("publishGps","gogogogo");
            }
        }
        public void stop(){
            stopped = true;

            try{
                Thread.sleep(3000); //2초씩 쉰다.
            }catch (Exception e) {}


        }

    }
    PublishThread publish ;
    Thread thread ;

    /**
     * Called when the activity is first created.
     */
    @Override
    public void onCreate(Bundle savedInstanceState) {

        super.onCreate(savedInstanceState);
        setContentView(R.layout.mainactivity);
        mContext = this;

        publish = new PublishThread();
        thread = new Thread(publish );


        lm = (LocationManager) getSystemService(Context.LOCATION_SERVICE);
        //textView1 = (TextView) findViewById(R.id.textView1);
        wifiApManager = new WifiApManager(this);
        //scan();
        try{
                // GPS 제공자의 정보가 바뀌면 콜백하도록 리스너 등록하기~!!!
                lm.requestLocationUpdates(LocationManager.GPS_PROVIDER, // 등록할 위치제공자
                        100, // 통지사이의 최소 시간간격 (miliSecond)
                        1, // 통지사이의 최소 변경거리 (m)
                        mLocationListener);
                lm.requestLocationUpdates(LocationManager.NETWORK_PROVIDER, // 등록할 위치제공자
                        100, // 통지사이의 최소 시간간격 (miliSecond)
                        1, // 통지사이의 최소 변경거리 (m)
                        mLocationListener);

        }catch(SecurityException ex){

        }
    }

    private final LocationListener mLocationListener = new LocationListener() {

        public void onLocationChanged(Location location) {
            //여기서 위치값이 갱신되면 이벤트가 발생한다.
            //값은 Location 형태로 리턴되며 좌표 출력 방법은 다음과 같다.

            Log.d("test", "onLocationChanged, location:" + location);
            values.longitude = location.getLongitude(); //경도
            values.latitude = location.getLatitude();   //위도

            double altitude = location.getAltitude();   //고도
            float accuracy = location.getAccuracy();    //정확도
            String provider = location.getProvider();   //위치제공자
            //Gps 위치제공자에 의한 위치변화. 오차범위가 좁다.
            //Network 위치제공자에 의한 위치변화
            //Network 위치는 Gps에 비해 정확도가 많이 떨어진다.

        }

        public void onProviderDisabled(String provider) {

            // Disabled시
            Log.d("test", "onProviderDisabled, provider:" + provider);
        }

        public void onProviderEnabled(String provider) {

            // Enabled시
            Log.d("test", "onProviderEnabled, provider:" + provider);
        }

        public void onStatusChanged(String provider, int status, Bundle extras) {

            // 변경시
            Log.d("test", "onStatusChanged, provider:" + provider + ", status:" + status + " ,Bundle:" + extras);
        }
    };

    // client.isReachable() 의 값을 항상 백그라운드에서 확인을 해서
    // openAP 를 누르면 그 시점부터 sleep(3000)간격으로 scan()실행

    public void scan() {

        wifiApManager.getClientList(false, new FinishScanListener() {

            @Override
            public void onFinishScan(final ArrayList<ClientScanResult> clients) {

                textView1.setText("WifiApState: " + wifiApManager.getWifiApState() + "\n\n");
                textView1.append("Clients: \n");
                for (ClientScanResult clientScanResult : clients) {
                    textView1.append("####################\n");
                    textView1.append("IpAddr: " + clientScanResult.getIpAddr() + "\n");
                    textView1.append("Device: " + clientScanResult.getDevice() + "\n");
                    textView1.append("HWAddr: " + clientScanResult.getHWAddr() + "\n");
                    textView1.append("isReachable: " + clientScanResult.isReachable() + "\n");

                    if (clientScanResult.isReachable() == true)
                        preFlag = true;

                    if (preFlag == true && clientScanResult.isReachable() == false) {
                        //message();
                        preFlag = false;
                    }

                }
            }
        });

    }

    public boolean onCreateOptionsMenu(Menu menu) {

        menu.add(0, 0, 0, "HotSpot");
        menu.add(0, 1, 0, "Disconnect");
        menu.add(0, 2, 0, "Nevigation");
        menu.add(0, 3, 0, "Location tracking Start");

        return super.onCreateOptionsMenu(menu);
    }
    // 나중에 0번 버튼은 삭제


    public boolean onMenuItemSelected(int featureId, MenuItem item) {

        switch (item.getItemId()) {

			/*
			case 0:
				scan();
				break;
			// 1번버튼(OpenAP)을 누르면 서비스실행 -> 실시간 스캔
			// 2번버튼(CloseAP)을 누르면 서비스 종료 ->스캔 종료
			*/

			case 0: {

                wifiApManager.setWifiApEnabled(null, true);
                Intent intent = new Intent(this, MyService.class);
                startService(intent);
                break;

            }
            case 1: {


               publish.stop();

                disconnect();

                wifiApManager.setWifiApEnabled(null, false);
                Intent intent = new Intent(this, MyService.class);
                stopService(intent);
                break;
            }
            case 2: {

                Intent intent = new Intent(this, Navigation.class);
                startActivity(intent);
                break;
            }
            case 3:{

                connectAction();
                thread.start();

            }
        }
        return super.onMenuItemSelected(featureId, item);
    }

    private void connectAction() {

        MqttConnectOptions conOpt = new MqttConnectOptions();
        // The basic client information
        String server = "192.168.43.48";
       // String server= "10.0.2.2";
        // String server ="10.0.1.9";frz
        String clientId = "jhy753";
        //int port =2001;
        int port = 7777;

        boolean cleanSession = false;

        String uri = "tcp://" + server + ":" + port;

        clientHandle = uri + clientId;

        Connection connection = Connection.getInstance();

        // connect client
        String[] actionArgs = new String[1];
        actionArgs[0] = clientId;
        connection.changeConnectionStatus(Connection.ConnectionStatus.CONNECTING);

        conOpt.setCleanSession(cleanSession);
        conOpt.setConnectionTimeout(60);
        conOpt.setKeepAliveInterval(60);

        final ActionListener callback = new ActionListener(this,
                ActionListener.Action.CONNECT, clientHandle, actionArgs);

        boolean doConnect = true;

        values.client.setCallback(new MqttCallbackHandler(this, clientHandle));

        //set traceCallback
        values.client.setTraceCallback(new MqttTraceCallback());

        connection.addConnectionOptions(conOpt);

        if (doConnect) {
            try {
                values.client.connect(conOpt, null, callback);
            }
            catch (MqttException e) {
                Log.e(this.getClass().getCanonicalName(),
                        "MqttException Occured", e);
            }
        }
    }


    public void disconnect() {

        Connection c = Connection.getInstance();
        //if the client is not connected, process the disconnect
        if (!c.isConnected()) {
            return;
        }
        try {

            values.client.disconnect(null, new ActionListener(this, ActionListener.Action.DISCONNECT, clientHandle, null));
            c.changeConnectionStatus(Connection.ConnectionStatus.DISCONNECTING);

        }
        catch (MqttException e) {
            Log.e(this.getClass().getCanonicalName(), "Failed to disconnect the client with the handle " + clientHandle, e);
            c.addAction("Client failed to disconnect");
        }


    }

    public void publishGps()
    {

        String topic1 ="GPS/latitude";
        String topic2 ="GPS/longitude";

        String latitudeValue = Double.toString( values.latitude );
        String longitudeValue =  Double.toString( values.longitude );

        int qos = ActivityConstants.defaultQos;

        qos = 0;
        //    qos = 1;
        // qos = 2;

        boolean retained = false;

        String[] args1 = new String[2];
        args1[0] = latitudeValue;
        args1[1] = topic1+";qos:"+qos+";retained:"+retained;

        String[] args2 = new String[2];
        args1[0] = longitudeValue;
        args1[1] = topic2+";qos:"+qos+";retained:"+retained;


        try {

            Log.d("gogoggo","gogogogogo");

            values.client.publish(topic1, latitudeValue.getBytes(), qos, retained, null, new ActionListener(this, ActionListener.Action.PUBLISH, values.clientHandle, args1));
            values.client.publish(topic2, longitudeValue.getBytes(), qos, retained, null, new ActionListener(this, ActionListener.Action.PUBLISH, values.clientHandle, args2));

        }
        catch (MqttSecurityException e) {
            Log.e(this.getClass().getCanonicalName(), "Failed to publish a messged from the client with the handle " + values.clientHandle, e);
        }
        catch (MqttException e) {
            Log.e(this.getClass().getCanonicalName(), "Failed to publish a messged from the client with the handle " + values.clientHandle, e);
        }
    }


}

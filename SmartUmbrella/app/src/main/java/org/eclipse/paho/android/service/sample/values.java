package org.eclipse.paho.android.service.sample;

import org.eclipse.paho.android.service.MqttAndroidClient;

/**
 * Created by hae young Joo on 2017-06-23.
 */

public class values {


    static double latitude;
    static double longitude;
    static String clientHandle;
    static MqttAndroidClient client = new MqttAndroidClient(MainActivity.mContext, "tcp://192.168.43.48:7777", "jhy753");;

}

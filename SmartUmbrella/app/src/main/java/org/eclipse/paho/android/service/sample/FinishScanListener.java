package org.eclipse.paho.android.service.sample;

import java.util.ArrayList;

/**
 * Created by hae young Joo on 2017-06-23.
 */

public interface FinishScanListener {

    /**
     * Interface called when the scan method finishes. Network operations should not execute on UI thread
     * @param  ArrayList of {@link ClientScanResult}
     */

    public void onFinishScan(ArrayList<ClientScanResult> clients);
}

package org.eclipse.paho.android.service.sample;

import android.app.Activity;
import android.os.Bundle;
import android.view.Menu;
import android.view.MenuItem;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;



/**
 * Created by song on 2017. 6. 15..
 */

public class Navigation extends Activity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.navigation);
        // localhost
        // String url = "http://10.0.2.2/";
        // my hotspot
        String url = "http://jhy753.dothome.co.kr";

        WebView webView = (WebView) findViewById(R.id.web1);
        webView.setWebViewClient(new WebViewClient());
        webView.getSettings().setLoadWithOverviewMode(true);
        webView.getSettings().setUseWideViewPort(true);

        WebSettings webSettings = webView.getSettings();
        webSettings.setJavaScriptEnabled(true);

        webView.loadUrl(url);
    }

    public boolean onCreateOptionsMenu(Menu menu) {
        //menu.add(0, 0, 0, "Connect");
        //menu.add(0, 1, 0, "Disconnect");
        menu.add(0, 0, 0, "Go Back");


        return super.onCreateOptionsMenu(menu);
    }
    // 나중에 0번 버튼은 삭제

    public boolean onMenuItemSelected(int featureId, MenuItem item) {
        switch (item.getItemId()) {

            case 0: {
                finish();
                break;
            }

        }
        return super.onMenuItemSelected(featureId, item);

    }

    class Browser extends WebViewClient {
        @Override
        public boolean shouldOverrideUrlLoading(WebView view, String url) {
            view.loadUrl(url);
            return super.shouldOverrideUrlLoading(view, url);
        }
    }

}

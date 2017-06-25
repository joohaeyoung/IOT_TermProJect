import RPi.GPIO as GPIO         # GPIO
import paho.mqtt.client as mqtt # MQTT
import pyowm                    # OpenWeatherMap
import time                     # TIme
import urllib2                  # URL Open Module
import sys                      # Python System Information Module
import os                       # Operating System Information Module
import requests                 # Http request Module
import threading                # Python Threading Module
from adxl345 import ADXL345     # ADXL345

# Start a thread to run mosquitto broker on port 7777
def broker(port) :
	os.system("mosquitto -p " + port)

t1 = threading.Thread(target=broker, args=('7777',))
t1.daemon = True # Start as Daemon
t1.start()

# Create a client who gets the weather condition
# Parameter requires a APP KEY
owm = pyowm.OWM('a766f243dff96643d5abda1111322204')

# Previous GPS Info
prev_lat = "0.0"
prev_lon = "0.0"

# Current GPS Info
curr_lat = "0.0"
curr_lon = "0.0"

# Absolute float GPS Info for getting weather condtion
# The default location is Chenan
ab_lat = 36.8065
ab_lon = 127.1522

# Current weather condition
weatherStatus = ""

# GPIO Pins mapping		
GPIO.setmode(GPIO.BCM)          # Set GPIO to BCM mode
pir_sensor = 23                 # PIR sensor
green_pin = 16                  # RGB LED Green
red_pin = 20                    # RGB LED Red
GPIO.setwarnings(False)         # Ignore GPIO Errors
GPIO.setup(pir_sensor, GPIO.IN) # Set PIR pin to input
GPIO.setup(red_pin, GPIO.OUT)   # Set RGB LED pin to output
GPIO.setup(green_pin, GPIO.OUT) # Set RGB LED pin to output

# Direct port of the Arduino NeoPixel library strandtest example.  Showcases
# various animations on a strip of NeoPixels.
from neopixel import *

# LED strip configuration
LED_COUNT      = 13                     # Number of LED pixels.
LED_PIN        = 18                     # GPIO pin connected to the pixels (18 uses PWM!).
#LED_PIN        = 10                    # GPIO pin connected to the pixels (10 uses SPI /dev/spidev0.0).
LED_FREQ_HZ    = 800000                 # LED signal frequency in hertz (usually 800khz)
LED_DMA        = 5                      # DMA channel to use for generating signal (try 5)
LED_BRIGHTNESS = 255                    # Set to 0 for darkest and 255 for brightest
LED_INVERT     = False                  # True to invert the signal (when using NPN transistor level shift)
LED_CHANNEL    = 0                      # set to '1' for GPIOs 13, 19, 41, 45 or 53
LED_STRIP      = ws.WS2811_STRIP_GRB    # Strip type and colour ordering

# Define functions which animate LEDs in various ways.
def colorWipe(strip, color, wait_ms=50):
	"""Wipe color across display a pixel at a time."""
	for i in range(strip.numPixels()):
		strip.setPixelColor(i, color)
		strip.show()
		time.sleep(wait_ms/1000.0)

def theaterChase(strip, color, wait_ms=50, iterations=10):
	"""Movie theater light style chaser animation."""
	for j in range(iterations):
		for q in range(3):
			for i in range(0, strip.numPixels(), 3):
				strip.setPixelColor(i+q, color)
			strip.show()
			time.sleep(wait_ms/1000.0)
			for i in range(0, strip.numPixels(), 3):
				strip.setPixelColor(i+q, 0)

def wheel(pos):
	"""Generate rainbow colors across 0-255 positions."""
	if pos < 85:
		return Color(pos * 3, 255 - pos * 3, 0)
	elif pos < 170:
		pos -= 85
		return Color(255 - pos * 3, 0, pos * 3)
	else:
		pos -= 170
		return Color(0, pos * 3, 255 - pos * 3)

def rainbow(strip, wait_ms=20, iterations=1):
	"""Draw rainbow that fades across all pixels at once."""
	for j in range(256*iterations):
		for i in range(strip.numPixels()):
			strip.setPixelColor(i, wheel((i+j) & 255))
		strip.show()
		time.sleep(wait_ms/1000.0)

def rainbowCycle(strip, wait_ms=20, iterations=5):
	"""Draw rainbow that uniformly distributes itself across all pixels."""
	for j in range(256*iterations):
		for i in range(strip.numPixels()):
			strip.setPixelColor(i, wheel((int(i * 256 / strip.numPixels()) + j) & 255))
		strip.show()
		time.sleep(wait_ms/1000.0)

def theaterChaseRainbow(strip, wait_ms=50):
	"""Rainbow movie theater light style chaser animation."""
	for j in range(256):
		for q in range(3):
			for i in range(0, strip.numPixels(), 3):
				strip.setPixelColor(i+q, wheel((i+j) % 255))
			strip.show()
			time.sleep(wait_ms/1000.0)
			for i in range(0, strip.numPixels(), 3):
				strip.setPixelColor(i+q, 0)

# Receive today weather condtion
def getWeatherStatus() :
        if isConnected() :
                global ab_lat
                global ab_lon
                o1 = owm.weather_at_coords(ab_lat, ab_lon)
                weat = o1.get_weather()
                return weat.get_status()
        else :
                return "Internet connection failed"                

# Check if the PIR sensor detects a motion or doesn't
def isDetectedByPIR(currState) :
        if currState : return True
        else : return False

# Check if the internet connection is on or off
def isConnected() :
        try :
                urllib2.urlopen('http://216.58.192.142', timeout = 1)
                return True
        except urllib2.URLError as err :
                return False

# MQTT callback for connection to broker
def on_connect(client, userdata, rc):
    print("Connected with broker result code " + str(rc))
    # Subscribe multiple messages using /#
    client.subscribe("GPS/#")

# A message callback to subscribe the tocd Ipic of GPS/latitude
def lat_message(client, userdata, msg):
    global curr_lat
    curr_lat = str(msg.payload)
    #print curr_lat
    
# A message callback to subscribe the topic of GPS/longitude
def lon_message(client, userdata, msg):
    global curr_lon
    curr_lon = str(msg.payload)
    #print curr_lon

# Connect to mqtt Server
client = mqtt.Client()
client.on_connect = on_connect

# This message_callback_add() handls multiple message callbacks
client.message_callback_add("GPS/latitude", lat_message)
client.message_callback_add("GPS/longitude", lon_message)

# YOU NEED TO CHANGE THE IP ADDRESS OR HOST NAME
client.connect("192.168.43.48", 7777)
client.loop_start()

if __name__ == '__main__':
    # Create NeoPixel object with appropriate configuration.
    strip = Adafruit_NeoPixel(LED_COUNT, LED_PIN, LED_FREQ_HZ, LED_DMA, LED_INVERT, LED_BRIGHTNESS, LED_CHANNEL, LED_STRIP)
    # Intialize the library (must be called once before other functions).
    strip.begin()
	
    try:
        time.sleep(0.5)
        print "Loading to Smart Umbrella System."
        time.sleep(0.5)
        print "Loading to Smart Umbrella System.."
        time.sleep(0.5)
        print "Loading to Smart Umbrella System..."
        time.sleep(0.5)
        print "Loading to Smart Umbrella System....Start!!"
        time.sleep(0.5)
        adxl345 = ADXL345()
        axes = adxl345.getAxes(True)
        weatherStatus = getWeatherStatus()     
        time.sleep(0.5)
        
        while True :
            print "Today weather condtion : " + weatherStatus
            if(weatherStatus == "Rain") :
                axes = adxl345.getAxes(True) 
                state = GPIO.input(pir_sensor)
                print "Gravitional Acceleration(Z axis) : %.3fG" % ( axes['z'] )
                if(axes['z'] > 0.9) :
                    if isDetectedByPIR(state) :
                        print "Motion detected!"
                        GPIO.output(green_pin, False)
                        for i in range(1, 6) :
                            #GPIO.output(red_pin, True)
                            #time.sleep(0.5)
                            #GPIO.output(red_pin, False)
                            #time.sleep(0.5)
                            colorWipe(strip, Color(255, 0, 0))
                        time.sleep(1)
                    else :
                        print "Wating for motion to be detected"
                        GPIO.output(red_pin, False)
                        GPIO.output(green_pin, True)
                    time.sleep(1)
                else :
                    print "The user is moving.."
                    time.sleep(1)
            else :
                time.sleep(2)
                
            #if previous latitude or longitude are not same as current latitude or longitude
            #consider them as valid values
            if(prev_lat != curr_lat or prev_lon != curr_lon) :
            # if latitude and longitude are not equal to zero, consider them as valid values
                if(curr_lat != "0.0" and curr_lon != "0.0") :
                    # HTTP get request for a query to insert current gps into the external database
		    if( isConnected() ) :                    
			r = requests.get("http://jhy753.dothome.co.kr/dbconfig.php?latitude="+curr_lat+"&longitude="+curr_lon)
                    	# Store current values to previous value variables
                    	prev_lat = curr_lat
                    	prev_lon = curr_lon
        
            # Display
            print "Current Location below : "
            print "---------------------------------"
            print "| latitude\t  : " + curr_lat + "\t|"
            print "| longitude\t  : " + curr_lon + "\t|"
            print "---------------------------------\n"
            
    except KeyboardInterrupt as e:
        print("Finished!")
        client.unsubscribe(["GPS/latitude", "GPS/longitude"])
        client.disconnect()
        GPIO.cleanup()

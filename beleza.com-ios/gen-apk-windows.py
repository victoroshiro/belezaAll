import os, shutil, errno

keystore_password = "beleza.playkey"
build = "ionic cordova build android --release --prod"
unsigned_apk = "C:\\Projetos\\beleza.com\\beleza.com\\platforms\\android\\app\\build\\outputs\\apk\\release\\app-release-unsigned.apk"
final_apk = "C:\\Projetos\\beleza.com\\beleza.com\\beleza.com.apk"
keystore = "beleza.keystore"
create_keystore = "keytool -genkey -v -keystore "+keystore+" -alias beleza -keyalg RSA -keysize 2048 -validity 10000"
jarsigner = "jarsigner -verbose -sigalg SHA1withRSA -digestalg SHA1 -keystore "+keystore+" "+unsigned_apk+" beleza"
zipalign = "C:\\Android\\Data\\build-tools\\27.0.3\\zipalign -v 4 "+unsigned_apk+" "+final_apk

if os.path.exists(unsigned_apk):
    os.remove(unsigned_apk)
if os.path.exists(final_apk):
    os.remove(final_apk)

os.system(build)
if not os.path.exists(keystore):
    os.system(create_keystore)
os.system('echo %s|%s' % (keystore_password, jarsigner))
os.system(zipalign)
import os, shutil, errno

keystore_password = "beleza-provider.playkey"
build = "ionic cordova build android --release --prod"
unsigned_apk = '"C:/Projetos/beleza.com/beleza.com-provider/platforms/android/app/build/outputs/apk/release/app-release-unsigned.apk"'
final_apk = '"C:/Projetos/beleza.com/beleza.com-provider/platforms/android/app/build/outputs/apk/release/beleza.com-provider.apk"'
keystore = "beleza-provider.keystore"
create_keystore = "keytool -genkey -v -keystore "+keystore+" -alias beleza-provider -keyalg RSA -keysize 2048 -validity 10000"
jarsigner = "jarsigner -verbose -sigalg SHA1withRSA -digestalg SHA1 -keystore "+keystore+" "+unsigned_apk+" beleza-provider"
zipalign = "C:/Android/Data/build-tools/27.0.3/zipalign -v 4 "+unsigned_apk+" "+final_apk

if os.path.exists(unsigned_apk):
    os.remove(unsigned_apk)
if os.path.exists(final_apk):
    os.remove(final_apk)

os.system(build)
if not os.path.exists(keystore):
    os.system(create_keystore)
os.system('echo %s|%s' % (keystore_password, jarsigner))
os.system(zipalign)
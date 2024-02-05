### Http Device

Mit dieser Instanz kann die erreichbarkeit eines Webdiestes überwacht werden. 

#### Einstellungen der Geräte-Instanz

##### URL 
IP-Adresse oder Hostname des Gerätes.

##### Zeitüberschretung 
Zeit, in der der Server die Anfrage beantworten kann bevor er als Offline angezeigt wird. 

##### Update Interval  
Intevall für die Anfragen an das Gerätes
0 deaktiviert das automatische Aktualisieren. 

##### Server Überprüfen
Prüft ob das Zertifikat zum Server passt

##### Zertifikat Überprüfen
Prüft die Zertifikatskette (Bei selbstsigniertem Zertifikat deaktivieren)



#### Variablen 

##### Online 
Zeigt ob das Gerät Online ist.
##### Inhalt 
Der Inhalt auf die Antwort des Servers. (Leer wenn keine Antwort erfolgt) 
##### Statuscode 
Statuscode des Servers. (0 wenn keine Antwort erfolgt)

#### Aktualisieren
```php
NET_Update(12345);
```


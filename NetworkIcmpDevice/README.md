### Generic Network Device

Mit dieser Instanz kann die erreichbarkeit eines belibigen Netzwerkgerätes überwacht werden. 

#### Einstellungen der Geräte-Instanz

##### Host 
IP-Adresse oder Hostname des Gerätes.

##### Timeout 
Timeout für die Ping-Anfrage.

##### Update Interval  
Intevall für Ping-Anfragen an das Gerätes

##### Retry Error 
Anzahl von Anfragen, die Negativ sein müssen bis das Gerät als Offline Markiert wird. 

##### Retry Ok
Anzahl von Anfragen, die Positiv sein müssen bis das Gerät als Online Markiert wird. 



#### Variablen 

##### Online 
Zeigt ob das Gerät Online ist.


#### Aktualisieren
```php
NET_Update(11111 /*[Test\SMSDevice]*/);
```


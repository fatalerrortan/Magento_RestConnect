Anwendbarkeit
1 Konfiguration im Admin

Bitte konfigurieren Sie zuerst Konten zur REST-Authentifikaton im Konfigurationsbereich: Admin-> System -> Configuration -> Nextorder Extensions(API REST Config) und stellen eine der gewünschten Rollen(Admin oder Customer) zum Einsatz stellen.
2 REST-Request stellen über diese Extension Nextorder_Restconnect => IndexController => index- oder customerAction()

REST-Request per Customer Rolle: http://www.magento-host.com/restconnect/index/index{mögliche Parameter}
REST-Request per Admin Rolle: http://www.magento-host.com/restconnect/index/admin{mögliche Parameter}
3 Ressouce Lokalisierung und Ausgabenformat über Parameter "query" und "form" per HTTP-GET

Beispiel: Ziel Ressource => Bestellung, Ziel Format => XML
http://www.magento-host.com/restconnect/index/{index oder admin}?query=order&format=xml

Beispiel: Ziel Ressource => Bestellung ID = 3, Ziel Format => Json
http://www.magento-host.com/restconnect/index/{index oder admin}?query=orders/5/items&format=json

Beispiel: Ziel Ressource => Kunden, Ziel Format => Json (wenn keine Angabe zur Format => Default XML)
http://www.magento-host.com/restconnect/index/{index oder admin}?query=customers&format=json

Alle zugreifbare Werte und original vordefinierte Syntax zu verschiedenen REST-Ressourcen für den Parameter "query" sehen sie http://devdocs.magento.com/guides/m1x/api/rest/introduction.html

Nehmen Sie an! Der originale REST URL: http://magento-host/api/rest/{Params} = Unsere Extension: http://www.magento-host.com/restconnect/index/{index oder admin}?query={Params}&form={xml oder json}
4 Request muss über HTTP-POST gestellt werden

Dabei muss man zwei Parameter "username" und "password" an die Extension per POST Methode implizit übertragen, um die Bereichtigung des Benutzers zu kontrollieren.

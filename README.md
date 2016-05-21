Anleitung

* Konfiguration für die Aktivierbarkeit und den Zugang zur Benutzung REST API
 
 - Admin -> System -> Konfiguration -> Nextorder Extensions(API REST Authentification)

* Konfiguration für die dynamischen Einträge wie (Steuer, ERP Payment Code, Accounting Key und Priorität der Zahlungsmethoden)

 - Admin -> System -> Konfiguration -> Nextorder Extensions(Order Result Config For REST)

* Base REST API URL  www.domain.com/restconnect/index/admin

 - API KEY und SECRET über POST implizit angeben.  Bitte nehmen Sie Parameternamen !"key" und "secret"!, sonst geht es nicht!
 
 - API Query und Result Form über GET explizit angeben => www.domain.com/restconnect/index/admin?query={...}&form={...}

 - Query Syntax:

  - Für alle Bestellungen => www.domain.com/restconnect/index/admin?query=nextorder/orders/all/item

  - Für einzelne Bestellung z.B. Bestellung 1388-16-105 => www.domain.com/restconnect/index/admin?query=nextorder/orders/1388-16-105/item

  - Für Match Scuhe Bitte nutzen Sie "pd" als Placeholder-Zeichen. z.B. Geben alle Bestellung, die mit 13 anfangen und im Jahr 2016 generiert werden, an => www.domain.com/restconnect/index/admin?query=nextorder/orders/13pd-16-105/item.

* restConnect.log protokolliert ganz Verfahren.

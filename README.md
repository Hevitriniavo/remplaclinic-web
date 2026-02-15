user
- numOrdre
- civility
- surname
- name
- yearOfBirth
- nationality
- email
- password
- status
- address_id
- telephone
- telephone_2
- fax
- position
- organism
- speciality_id
- sub_specialities [id]
- yearOfResidency
- current_speciality_id [int]
- mobility_id
- clinic
- comment [commentaires_remplacant or field_commentaire_user]

- id
- profile_photo
- recuperation_code
- roles
- cv
- replacement_licence
- diplom
- subscription_status
- subscription_end
- installation
- contact
- subscription_end_email
- chief_of_service_name
- send_replacement_licence
- cgu

user_role
- id
- role [Remplaçant]


user_address
- id
- country
- locality
- postal_code
- thoroughfare
- premise

user_establishment
-id
- etablishment_name
- beds_count
- site_internet
- consultation_count
- per


requests
- applicant [ManyToOne -> User]
- title (varchar)
- status [enum: a valider, en cours, archive]
- startedAt [datetime]
- showEndAt [boolean]
- endAt [datetime]
- lastSentAt [datetime]
- region [ManyToOne -> Region]
- speciality [ManyToOne -> Speciality]
- requestType [enum: replacement/installation]
- remuneration [string]
- comment [text]

- subSpecialities [ManyToMany -> Speciality]
- positionCount [integer]
- accomodationIncluded [enum: oui/non/a debattre]
- transportCostRefunded [enum: oui/non/a debattre]
- retrocession [string]
- replacementType [enum: regular/ponctuel]

- raison [string]
- raisonValue [string]

annoce_histories
- date

request_responses
- status [enum: en cours/accepte/infos_plus]
- user [ManyToOne -> User]
- request [ManyToOne -> Request]
- updatedAt [datetime]
- createdAt [datetime]


TODO: (27/12/2025)
- [DONE] end date for import request
- [DONE] raison value for import request
- [API] optimize get all request (caused by get response & get roles)
- [JS] fix URL redirect for request table
- [JS] changer title en tooltip
- [DONE] [Import] separate import requests and candidatures
- Interface pour l'import des donnees
- mail system
    - [Done] retrieve sender email from env
- authentification
- fix all select user [make server data load]

- [import] Add support for log and event
    * auto actualise the table to check the status
    * Retry system

TODO: (15/02/2026)
- make vue to use import map
- make axios to use import map
- add QA test

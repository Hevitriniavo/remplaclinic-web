sudo supervisorctl reread

sudo supervisorctl update

sudo supervisorctl start rempla-messenger-consume:*


// after deploy
sudo supervisorctl restart rempla-messenger-consume:*

// see status
sudo supervisorctl status rempla-messenger-consume:*
sudo supervisorctl reread

sudo supervisorctl update

sudo supervisorctl start rempla-messenger-consume:*
sudo supervisorctl start rempla-cron-consume:*

// after deploy
sudo supervisorctl restart rempla-messenger-consume:*
sudo supervisorctl restart rempla-cron-consume:*

// see status
sudo supervisorctl status rempla-messenger-consume:*
sudo supervisorctl status rempla-cron-consume:*
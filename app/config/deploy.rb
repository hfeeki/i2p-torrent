set :application, "torrent"
set :domain,      "locke.drollette.com"
set :deploy_to,   "/home/matt/hidden/torrent"
set :app_path,    "app"
set :branch,      "master"
set :symfony_env_prod, "prod"

set :repository,  "git@github.com:MDrollette/Torrent.git"
set :scm,         :git

set :model_manager, "doctrine"

role :web,        domain                         # Your HTTP server, Apache/etc
role :app,        domain                         # This may be the same as your `Web` server
role :db,         domain, :primary => true       # This is where Rails migrations will run

set  :keep_releases,  3

set   :use_sudo,      false
ssh_options[:port] =  29123

set :user, "matt"

set :update_vendors, false
set :use_composer, true
set :dump_assetic_assets, true

set :shared_files,      ["app/config/parameters.yml"]
set :shared_children,   [app_path + "/logs", app_path + "/spool"]
# hrrn_petfinder

# Setup Dev Environment
1. Run bash setup.sh from project root.
   * This creates and downloads a wp installation in project root directory.  Only used by phpstorm as reference.
2. Run `docker-compose build`, then `docker-compose up`
3. Navigate to http://localhost:8000 and install
    * db = wordpress
    * user = wordpress
    * pass = wordpress
    * host = db
4. Import site using All-In-One  
5. Setup path mappings in phpstorm
    * ./wordpress -> /var/www/html/
    * ./hrrn_petfinder -> /var/www/html/wp-content/plugins/hrrn_petfinder
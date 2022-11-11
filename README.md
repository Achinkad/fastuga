## Fastuga Project

[03/01-DAD] SAP Web Application to manage a restaurant.

### Dependecies

Make sure that your system has the following dependecies installed.

- npm / nodejs
- php >= 8.0
- mysql / mariadb
- composer

### Installation

Follow this steps to make an installation of this project in your system.

```shell
$ git clone https://github.com/joseareia/fastuga
```

Next, with Apache or Nginx, create a web server to support the Laravel API.

**Note**: If you're running Windows you can use Laragon or Docker + Sail to set up the Laravel API.

### Usage

After a successful Nginx/Apache configuration in your system you can now access your Laravel API throught the server name of your choice.

To set up the client, run the following.

```shell
$ cd fastuga-client
$ npm install
$ npm run dev
```

Now, you can develop and make changes in this project.

**Note**: In order to access to the Laravel API (Database), make sure to copy the file `.env.example` to `.env` and make the necessary changes (e.g. DB_DATABASE, DB_USERNAME, DB_PASSWORD).

Finally, run `npm run build` to make a final version of the project.

### License

This project is under the [GPL-3.0 license](https://www.gnu.org/licenses/gpl-3.0.en.html).

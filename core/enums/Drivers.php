<?php

namespace core\enums;

enum Drivers: string
{
    case CUBRID = "cubrid";
    case DBLIB = "dblib";
    case FIREBIRD = "firebird";
    case IBM = "ibm";
    case INFORMIX = "informix";
    case MYSQL = "mysql";
    case OCI = "oci";
    case ODBC = "odbc";
    case PGSQL = "pgsql";
    case SQLITE = "sqlite";
    case SQLSRV = "sqlsrv";
}

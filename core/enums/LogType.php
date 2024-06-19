<?php

namespace core\enums;

enum LogType
{
    case Debug;
    case Info;
    case Warning;
    case Error;
    case Critical;
    case Alert;
    case Emergency;
}

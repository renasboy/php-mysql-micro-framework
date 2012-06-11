<?php
// in case the database is setup
if (!empty($hello_world)) {
    print $hello_world[0]->name;
    print ' from db using api';
}
else {
    print 'Hello World';
}

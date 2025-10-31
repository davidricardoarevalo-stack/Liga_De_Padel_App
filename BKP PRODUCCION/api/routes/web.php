<?php
/**
 * Routes mapping to keep contracts compatible with Node backend
 */
$router = $app->router;

$router->post('/login', 'AuthController@login');
$router->post('/register', 'AuthController@register');
$router->get('/profile', ['middleware' => 'auth', 'uses' => 'AuthController@profile']);
$router->get('/debug', ['middleware' => 'auth', 'uses' => 'AuthController@debug']);

// Athletes
$router->get('/athletes', ['middleware' => 'auth', 'uses' => 'AthleteController@index']);
$router->post('/athletes', ['middleware' => 'auth', 'uses' => 'AthleteController@store']);
$router->put('/athletes/{id}', ['middleware' => 'auth', 'uses' => 'AthleteController@update']);
$router->delete('/athletes/{id}', ['middleware' => 'auth,role:Administrador', 'uses' => 'AthleteController@destroy']);

// Clubs
$router->get('/clubs', ['middleware' => 'auth', 'uses' => 'ClubController@index']);
$router->post('/clubs', ['middleware' => 'auth', 'uses' => 'ClubController@store']);
$router->put('/clubs/{id}', ['middleware' => 'auth', 'uses' => 'ClubController@update']);
$router->delete('/clubs/{id}', ['middleware' => 'auth,role:Administrador', 'uses' => 'ClubController@destroy']);

// Tournaments
$router->get('/tournaments', 'TournamentController@index');
$router->post('/tournaments', ['middleware' => 'auth', 'uses' => 'TournamentController@store']);
$router->put('/tournaments/{id}', ['middleware' => 'auth', 'uses' => 'TournamentController@update']);
$router->delete('/tournaments/{id}', ['middleware' => 'auth,role:Administrador', 'uses' => 'TournamentController@destroy']);

// Users
$router->get('/users', ['middleware' => 'auth,role:Administrador', 'uses' => 'UserController@index']);
$router->post('/users', ['middleware' => 'auth,role:Administrador', 'uses' => 'UserController@store']);
$router->put('/users/{id}', ['middleware' => 'auth,role:Administrador', 'uses' => 'UserController@update']);
$router->delete('/users/{id}', ['middleware' => 'auth,role:Administrador', 'uses' => 'UserController@destroy']);

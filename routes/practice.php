<?php

use Illuminate\Support\Facades\DB;

//execute same query and get same data 
$actor = DB::table('actor')
    ->where('last_name', 'Berry')
    ->where('first_name', 'karl')
    ->get();

$actor = DB::table('actor')
    ->where([
        ['last_name', 'Berry'],
        ['first_name', 'karl']
    ])
    ->get();

$actor = DB::table('actor')
    ->where('last_name', 'Berry')
    ->where('first_name', 'karl')
    ->get();

$actor = DB::table('actor')
    ->where(function ($query) {
        $query->where('first_name', 'karl')
            ->where('last_name', 'Berry');
    })
    ->get();
//execute same query and get same data 

$actor = DB::table('actor')
    ->select('last_name', DB::raw('COUNT(*) AS actor_count'))
    ->groupBy('last_name')
    ->orderBy('actor_count', 'desc')
    ->get();
$actor = DB::table('country')
    ->select('country_id', 'country')
    ->whereIn('country', ['Bangladesh', 'Afghanistan', 'China'])
    ->orderBy('country_id', 'desc')
    ->get();


/*
SELECT film_id, title, special_features, replacement_cost from film WHERE replacement_cost BETWEEN 18.99 AND 20.99 ORDER BY film_id LIMIT 10;
*/

$actor = DB::table('film')
    ->select('film_id', 'title', 'special_features', 'replacement_cost')
    ->whereBetween('replacement_cost', [18.99, 20.99])
    ->orderBy('film_id', 'desc')
    ->limit(10)
    ->get();


$actor = DB::table('film')
    ->select('film_id', 'title', 'special_features', 'replacement_cost')
    ->whereNotBetween('replacement_cost', [18.99, 20.99])
    ->orderBy('film_id')
    ->limit(10)
    ->get();

$actor = DB::table('film')
    ->select('film_id', 'title', 'special_features', 'replacement_cost')
    ->where('title', 'AFRICAN EGG')
    ->orWhere('title', 'AGENT TRUMAN')
    ->limit(10)
    ->get();

/* 
    SELECT s.staff_id, s.first_name, s.last_name, s.email, addr.address, addr.district, addr.postal_code, c.city, count.country from staff as s LEFT JOIN address as addr ON s.address_id = addr.address_id LEFT JOIN city as c ON addr.address_id = c.city_id LEFT JOIN country as count ON c.country_id = count.country_id;
*/

$actor = DB::table('staff AS s')
    ->select([
        's.staff_id', 's.first_name', 's.last_name', 's.email',
        'addr.address', 'addr.district', 'addr.postal_code',
        'c.city', 'count.country'
    ])
    ->leftJoin('address AS addr', 's.address_id', '=', 'addr.address_id')
    ->leftJoin('city AS c', 'addr.city_id', '=', 'c.city_id')
    ->leftJoin('country AS count', 'c.country_id', '=', 'count.country_id')
    ->orderBy('staff_id')
    ->get();
/*
SELECT store_details.*, payment_details.*

from (
	SELECT sto.store_id, city.city, count.country
    from store as sto
    LEFT JOIN address as addr
    ON sto.address_id = addr.address_id
    JOIN city
    ON addr.city_id = city.city_id
    JOIN country as count
    ON city.country_id = count.country_id
) as store_details

INNER JOIN (
	SELECT cust.store_id, sum(pay.amount) as sales
    from customer as cust
    JOIN payment as pay
    ON cust.customer_id = pay.customer_id
    GROUP BY cust.store_id
) as payment_details

ON store_details.store_id = payment_details.store_id
ORDER BY store_details.store_id;

*/

// first sub query example

$store_details = DB::query()
    ->select('sto.store_id', 'city.city', 'count.country')
    ->from('store as sto')
    ->leftJoin('address as addr', 'sto.address_id', '=', 'addr.address_id')
    ->join('city', 'addr.city_id', '=', 'city.city_id')
    ->join('country as count', 'city.country_id', '=', 'count.country_id');


$payment_details = DB::query()
    ->select('cust.store_id', DB::raw('SUM(pay.amount) as sales'))
    ->from('customer as cust')
    ->join('payment as pay', 'cust.customer_id', '=', 'pay.customer_id')
    ->groupBy('cust.store_id');


$result = DB::query()
    ->select('store_details.*', 'payment_details.*')
    ->fromSub($store_details, 'store_details')
    ->joinSub($payment_details, 'payment_details', 'store_details.store_id', '=', 'payment_details.store_id')
    ->get();

return $result;

//2dn sub query example

$store_details = DB::query()
    ->select('sto.store_id', 'city.city', 'count.country')
    ->from('store as sto')
    ->leftJoin('address as addr', 'sto.address_id', '=', 'addr.address_id')
    ->join('city', 'addr.city_id', '=', 'city.city_id')
    ->join('country as count', 'city.country_id', '=', 'count.country_id');


$payment_details = DB::query()
    ->select('cust.store_id', DB::raw('SUM(pay.amount) as sales'))
    ->from('customer as cust')
    ->join('payment as pay', 'cust.customer_id', '=', 'pay.customer_id')
    ->groupBy('cust.store_id');


$result = DB::query()
    ->select('store_details.*', 'payment_details.*')
    ->fromSub(function ($query) {
        $query->select('sto.store_id', 'city.city', 'count.country')
            ->from('store as sto')
            ->leftJoin('address as addr', 'sto.address_id', '=', 'addr.address_id')
            ->join('city', 'addr.city_id', '=', 'city.city_id')
            ->join('country as count', 'city.country_id', '=', 'count.country_id');
    }, 'store_details')
    ->joinSub(function ($query) {
        $query->select('cust.store_id', DB::raw('SUM(pay.amount) as sales'))
            ->from('customer as cust')
            ->join('payment as pay', 'cust.customer_id', '=', 'pay.customer_id')
            ->groupBy('cust.store_id');
    }, 'payment_details', 'store_details.store_id', '=', 'payment_details.store_id')
    ->get();

return $result;


/*
SELECT cat.name, COUNT(f.film_id) as film_count
FROM category as cat
LEFT JOIN film_category as fc
ON cat.category_id = fc.category_id
JOIN film as f
ON fc.film_id = f.film_id
JOIN language as lang
ON f.language_id = lang.language_id
WHERE lang.name = 'English'
GROUP BY cat.name
ORDER BY film_count;

*/

$result = DB::query()
    ->select('cat.name', DB::raw('COUNT(f.film_id) as film_count'))
    ->from('category as cat')
    ->leftJoin('film_category as fc', 'cat.category_id', '=', 'fc.category_id')
    ->join('film as f', 'fc.film_id', '=', 'f.film_id')
    ->join('language as lang', function ($join) {
        $join->on('f.language_id', 'lang.language_id')
            ->where('lang.name', 'English');
    })->groupBy('cat.name')
    ->orderBy('film_count', 'DESC')
    ->get();

return $result;


/*
SELECT film_id, title
FROM film
WHERE title LIKE 'K%' OR title LIKE 'Q%'
AND language_id IN (
	SELECT language_id from language 
     WHERE name = 'English'
)
ORDER BY title
*/

$result = DB::table('film')
    ->select('film_id', 'title')
    ->where('title', 'LIKE', 'K%')
    ->orWhere('title', 'LIKE', 'Q%')
    ->whereIn('language_id', function ($query) {
        $query->select('language_id')->from('language')
            ->where('name', 'English');
    })->orderBy('title')->get();

return $result;

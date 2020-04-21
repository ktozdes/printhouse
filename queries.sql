
SELECT name, plate_id , sum(quantity), count(id) FROM `storages` 
    where name = 'storage' 
    GROUP by plate_id 

union 

SELECT name, plate_id , sum(quantity), count(id) FROM `storages`
    where name = 'order' 
    GROUP by plate_id

ORDER BY `plate_id` ASC



select inp.plate_id, inp.id, inp.quantity, inp.updated_at, min_value from storages as inp 
left join 
    (select used_storage_id, min(local_quantity_after) as min_value from storages group by used_storage_id) 
        as outp on inp.id = outp.used_storage_id 
where inp.name = 'storage' and plate_id = 1 and (min_value > 0 OR min_value is null)

update payments set manager_id = 1 where 1



select orders.id, storage.storage_id, storage.plate_id, plate.price as plate_price, plate_user.price as user_price from orders
left join (select id as storage_id, order_id, plate_id as plate_id from storages where order_id = 101 LIMIT 1)as storage on storage.order_id = orders.id
left join plates as plate on plate.id = storage.plate_id
left join plate_user on plate_user.user_id = orders.user_id and plate_user.plate_id = storage.plate_id

where orders.id = 101




SELECT orders.user_id, sum(payments.amount) as amount, sum(storage.quantity) as quantity, income_payment.income_amount
FROM `orders`  

inner join (SELECT order_id, SUM(quantity) as quantity
    FROM storages 
    group by order_id) as storage on storage.order_id = orders.id

inner join (SELECT user_id as user_id, SUM(amount) as income_amount
    FROM payments
    where name = 'payment'
    group by user_id) as income_payment on income_payment.user_id = orders.user_id

inner join payments on orders.payment_id = payments.id

where orders.created_at >= '2000/3/1 00:00:01'  
group by orders.user_id
ORDER BY `orders`.`user_id`  DESC



SELECT storages.name, sum(storages.quantity) as quantity, plates.name FROM `storages` 
inner join plates on plates.id = storages.plate_id
where storages.name = 'order' OR storages.name = 'defect'
group by storages.name, plates.name
ORDER BY plates.name
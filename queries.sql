
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
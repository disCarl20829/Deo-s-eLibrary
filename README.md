**--//MYSQL DATABASE\\--**<br>
Match your database and tables exactly.<br>
<br>
_[book_category]_<br>
category_id (not pri key/auto inc) <br>
book_id <br>
<br>
_[customer_feedbacks]_<br>
feedback_id (pri key)<br>
user_id<br>
feedback_message<br>
feedback_rating<br>
feedback_date<br>
<br>
_[save_books]_<br>
saved_id (pri key)<br>
user_id<br>
book_id<br>
save_quantity<br>
<br>
_[shop]_<br>
shop_id (pri key)<br>
shop_owner<br>
shop_history<br>
shop_mission<br>
shop_vision<br>
shop_img_path<br>
<br>
_[users]_<br>
user_id<br>
user_name<br>
user_password<br>
user_description (redacted)<br>
<br>
_[books]_<br>
book_id<br>
book_title<br>
book_author<br>
book_pubdate<br>
book_description<br>
book_price<br>
book_img_path<br>
<br>
**--//END\\--**<br>
<br>
- You must copy all files including folders for the images<br>
- Modify the **$db** of your **db_connect** with the exact database name (e.g mine was $db = "elibrary")<br>

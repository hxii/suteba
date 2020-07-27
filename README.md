# Suteba

*Suteba* (捨場 - dumping ground) is a simple request bin/dump written in PHP.

I needed this functionality for my day job, and

while I could've used something that's available (and probably better), that wouldn't be much of a learning experience now, would it?

The solution? Make your own.

## Usage

1. Upload the file to folder/subdomain of your choosing.
2. Add allowed keys to the file under `$keys`, e.g.:
```php
private  $keys = [
'test' => '098f6bcd4621d373cade4e832627b4f6',
'hxii' => '4b58f3d6a1935c14ac594f9ef9dca69e'
];
```
3. Send your request to the file, e.g
`curl -d "test=true&value=suetba%20is%20cool" https://yourdomain/pb.php?key=098f6bcd4621d373cade4e832627b4f6`
4. If the key matches, you'll get a response with the ID and path of the request, e.g.:
```json
{
"id": "e08b7ecac70c8142ff2c8e76ebd17892",
"path": "data/098f6bcd4621d373cade4e832627b4f6/e08b7ecac70c8142ff2c8e76ebd17892.txt"
}
```
5. You can access the request either:
   1. Directly using `https://yourdomain.com/data/098f6bcd4621d373cade4e832627b4f6/e08b7ecac70c8142ff2c8e76ebd17892.txt` or
   2. You can list all available requests for a key using `https://yourdomain/pb.php?key=098f6bcd4621d373cade4e832627b4f6&action=list`.
## Issues and such

Found a problem? Report it [here](https://todo.sr.ht/~hxii/suteba).
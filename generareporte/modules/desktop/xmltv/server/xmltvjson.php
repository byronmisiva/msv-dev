<?php
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');

    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Content-type: text/html; charset=utf-8');
}

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
}

//////////////////////////

include("mysql.class.php");

$databaseXmltv = new MySQL();
if ($databaseXmltv->Query("SELECT xmltv_canal.id,
                                    xmltv_canal.nombre,
                                    xmltv_canal.file
                                     FROM xmltv_canal WHERE activo = 1 ORDER BY orden")
) {
    $canales = $databaseXmltv->GetJSON();
} else {
    $canales = "''";
};
//CONCAT(UNIX_TIMESTAMP (fecha_inicio), '000') as inicio,
CONCAT(UNIX_TIMESTAMP (fecha_fin), '000') as fin,

if ($databaseXmltv->Query("SELECT id,
                                id_canal,
                                     titulo,
                                     descripcion,
                                      tipo,
                                      CONCAT(UNIX_TIMESTAMP (fecha_inicio), '000') as inicio,
                                      CONCAT(UNIX_TIMESTAMP (fecha_fin), '000') as fin,
                                     duracion,
                                     file
                                     FROM xmltv_programa WHERE activo = 1 ORDER BY id_canal")
) {
    $programas = $databaseXmltv->GetJSON();
} else {
    $programas = "''";
};

//////////////////////////

?>
[{
"categoria": "canal",
"items":<?php echo $canales; ?>
},
{
"categoria": "programa",
"items": [{
"id": "1",
"id_canal": "1",
"titulo": "Noticia 1",
"descripcion": "orem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived no",
"tipo": "Serie",
"inicio": "<?php $datetime = new DateTime(date('Y-m-d  12:00:00'));
echo $datetime->format('U') . '000'; ?>",
"fin": "<?php $datetime = new DateTime(date('Y-m-d  13:00:00'));
echo $datetime->format('U') . '000'; ?>",
"duracion": "30",
"file": "data:image\/gif;base64,iVBORw0KGgoAAAANSUhEUgAAADcAAAA3CAIAAAAnuUURAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJ\r\nbWFnZVJlYWR5ccllPAAAA3hpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw\/eHBhY2tldCBiZWdp\r\nbj0i77u\/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6\r\neD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMDY3IDc5LjE1\r\nNzc0NywgMjAxNS8wMy8zMC0yMzo0MDo0MiAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJo\r\ndHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlw\r\ndGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEu\r\nMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVz\r\nb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1N\r\nOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDo0MjdjM2JiNC05Njk4LTRjZTctYWYyOS1mNTZi\r\nMmFlMGVkYzgiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6QkZGQUEyNTJCMjQyMTFFNUFFODc5\r\nNTE2NENDMUU3MDMiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6QkZGQUEyNTFCMjQyMTFFNUFF\r\nODc5NTE2NENDMUU3MDMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUg\r\nKE1hY2ludG9zaCkiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlp\r\nZDo0MjdjM2JiNC05Njk4LTRjZTctYWYyOS1mNTZiMmFlMGVkYzgiIHN0UmVmOmRvY3VtZW50SUQ9\r\nInhtcC5kaWQ6NDI3YzNiYjQtOTY5OC00Y2U3LWFmMjktZjU2YjJhZTBlZGM4Ii8+IDwvcmRmOkRl\r\nc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+atCV\r\npQAABZtJREFUeNrsmluME1UYx+fMtZ1O2+22C6XLLisuLsomq3GFDctFNCEQJNEnEh4MolETH3wi\r\nxhijkUQNxvCiAYwxQAxREonxwsUgFxfEBwwuASOyYdldWrptp8tMZ6YznZkzfkObsl1WCArsoD2Z\r\nhzNnpjO\/+Z\/\/950zp4McxyE8X+hqzbbtVCqVy+WKxWKpVJoqIJZl\/X5\/LBZLJBIURZUbUVlLIOvv\r\n7w+FQvF4XBAEhmEQQncfEWBM01QUJZ1Oy7Lc1dUFxBVKKCdPngT25uZm7\/RyMpmEvu3u7ga9SNgX\r\nRRG09RQiFOABKmCDuksJXmxqavJg0ADVNUpVVcPhsAcpgQo8WqE0DIPjOA9SApWu6xVKy7JomvZi\r\nmqRpYKtQYoyrmclTBajKiZIk7oVSp7y9lND9MIh7EK4a1i4lDEEenxnVfVmnrFP+tyknTjKwY2QL\r\nh2FiX57h310YdDUtMk3BZSTi\/pZSKw3llePx8GqaCv7LG5pHRqj2BkcxqbmNt\/Az7FiOkhr7qlHo\r\n5dlZk\/Q4dnRROTazcW0V0cZ67RVKDoHHvUnZuCJ5pQH6obojL9ttnxGtY8kbvYtp5oQW47MzaMAE\r\nBiABnkm0zMgHEw3PlOslS\/w99bqk9UeDSzqbPwCaPy6\/nZUPIkS1Rte1xV4czG4dye+Ah1zYvo+m\r\nQqPS9+dHN9m4GOYfmdf8PkNF3Kca0+1h2T6dVZ47AHX26fbSnvPkNF7YvgJfVpX1B3BapTtjwher\r\n8MAV9dXDBEcJny6nOlztgQR4YoEV1\/c4pki+XBvIfOhnWro6tjiEO75fyu8yzNFFDxyy7MKvQ+vA\r\nOmnpmwWzv2boCIlYw0z\/mX63q\/WToG\/uufTGgdHNDybecaUa0\/FwQXvrZ3ZNB90zQ176ZfjEWvPQ\r\nsPbmcfADsILYTr6oPv8DTin85sdxStVe+yl0ZI07tXBJ8KQxfu0FXDUuTA+vBOUA4mxyg1Q8BY6m\r\nyADHxBv4RwvFMw18N8dMh6Np6bvB3BYf2xLyz4Pzp4WWq8ZATbeO6fT8OLNkpitJzwx6YQKYCNth\r\nV9+PByX6yVbrbA7niszimfSCuHPFmJSHHOczq2q7qLDoQuYjqfjbsLjjigaIT4zkd+XVE6PyPlHp\r\ng\/DKKUcy8gEItcHsxw38fJBzJP+5pJ26mN0WFXpvEskcRbYEtQ1HufWdxrbT7Mr7JrEsRIFjTeJL\r\nCKtRaW88\/BTU22IvDRHU+fQmEOzh1i0Bbg5Y82J2K0n6wKaRwIJ56L3h\/E6M9bbYy\/HwKoGbA4pm\r\n5H1RYems2AvudbuaUNRPtgYJmkQCW25xEQWWbI8IO3q0N46pr\/xIL27mN\/baQ3sICiE\/TXZUEgKQ\r\nNI5\/Wpiz9fX1maYJlbzyC3jWmeoCDEBSXo0BNqjU5EsQSSsNJ8d2U6SfQj6EJuZ8uiSQz46Y+y8i\r\nngZVIEhvIWWHOUcyCBIxK9rwzhaLVa5bJLJsR4dEEQn08GzrjcYeODzhjJp8tv2ssnfQjcGHomQT\r\n7+gW3NW9gVyiOiKOqNsjBTwkM70JrFmIQuAv6Ef7XJ7qjIHTzKOXANTcOyh828Gte+yfj5A30SPq\r\nqyStbBE2MuJDAgN5zvW6YgKiUyi5gvloQtQJ1o0SFGKhEYtFfElxD9Ve545QQvrgNy0191+AVHKt\r\ntWSDARwRoDkiwlEQMaqJfNTVNAQPo0FKQe54EHQPUdDjs+E6d5ASin9DN2z1mVudsk5Zp7zNlDBQ\r\nTsn\/JrdGadu2N1dZa9aC6778v1GSJOnNVVagKoc1Od6kXis1a8EcxxmG4UFKoPL5fBXKQCAgSZIH\r\nKQuFArBVKGOxWDab9SBlJpMBtgplNBotf2jgKUTgAV8CG3EvfWtQDftkMimKoqZpcOpUSQgC8TwP\r\nEpb\/xa+8zd0T38D8JcAA87pOsBMh4hsAAAAASUVORK5CYII=\r\n"
},
{
"id": "2",
"id_canal": "1",
"titulo": "Programa 2 canal 1",
"descripcion": "orem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived no",
"tipo": "Serie",
"inicio": "<?php $datetime = new DateTime(date('Y-m-d  13:00:00'));
echo $datetime->format('U') . '000'; ?>",
"fin": "<?php $datetime = new DateTime(date('Y-m-d  14:00:00'));
echo $datetime->format('U') . '000'; ?>",
"duracion": "30",
"file": "data:image\/gif;base64,iVBORw0KGgoAAAANSUhEUgAAADcAAAA3CAIAAAAnuUURAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJ\r\nbWFnZVJlYWR5ccllPAAAA3hpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw\/eHBhY2tldCBiZWdp\r\nbj0i77u\/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6\r\neD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMDY3IDc5LjE1\r\nNzc0NywgMjAxNS8wMy8zMC0yMzo0MDo0MiAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJo\r\ndHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlw\r\ndGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEu\r\nMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVz\r\nb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1N\r\nOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDo0MjdjM2JiNC05Njk4LTRjZTctYWYyOS1mNTZi\r\nMmFlMGVkYzgiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6QkZGQUEyNTJCMjQyMTFFNUFFODc5\r\nNTE2NENDMUU3MDMiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6QkZGQUEyNTFCMjQyMTFFNUFF\r\nODc5NTE2NENDMUU3MDMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUg\r\nKE1hY2ludG9zaCkiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlp\r\nZDo0MjdjM2JiNC05Njk4LTRjZTctYWYyOS1mNTZiMmFlMGVkYzgiIHN0UmVmOmRvY3VtZW50SUQ9\r\nInhtcC5kaWQ6NDI3YzNiYjQtOTY5OC00Y2U3LWFmMjktZjU2YjJhZTBlZGM4Ii8+IDwvcmRmOkRl\r\nc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+atCV\r\npQAABZtJREFUeNrsmluME1UYx+fMtZ1O2+22C6XLLisuLsomq3GFDctFNCEQJNEnEh4MolETH3wi\r\nxhijkUQNxvCiAYwxQAxREonxwsUgFxfEBwwuASOyYdldWrptp8tMZ6YznZkzfkObsl1WCArsoD2Z\r\nhzNnpjO\/+Z\/\/950zp4McxyE8X+hqzbbtVCqVy+WKxWKpVJoqIJZl\/X5\/LBZLJBIURZUbUVlLIOvv\r\n7w+FQvF4XBAEhmEQQncfEWBM01QUJZ1Oy7Lc1dUFxBVKKCdPngT25uZm7\/RyMpmEvu3u7ga9SNgX\r\nRRG09RQiFOABKmCDuksJXmxqavJg0ADVNUpVVcPhsAcpgQo8WqE0DIPjOA9SApWu6xVKy7JomvZi\r\nmqRpYKtQYoyrmclTBajKiZIk7oVSp7y9lND9MIh7EK4a1i4lDEEenxnVfVmnrFP+tyknTjKwY2QL\r\nh2FiX57h310YdDUtMk3BZSTi\/pZSKw3llePx8GqaCv7LG5pHRqj2BkcxqbmNt\/Az7FiOkhr7qlHo\r\n5dlZk\/Q4dnRROTazcW0V0cZ67RVKDoHHvUnZuCJ5pQH6obojL9ttnxGtY8kbvYtp5oQW47MzaMAE\r\nBiABnkm0zMgHEw3PlOslS\/w99bqk9UeDSzqbPwCaPy6\/nZUPIkS1Rte1xV4czG4dye+Ah1zYvo+m\r\nQqPS9+dHN9m4GOYfmdf8PkNF3Kca0+1h2T6dVZ47AHX26fbSnvPkNF7YvgJfVpX1B3BapTtjwher\r\n8MAV9dXDBEcJny6nOlztgQR4YoEV1\/c4pki+XBvIfOhnWro6tjiEO75fyu8yzNFFDxyy7MKvQ+vA\r\nOmnpmwWzv2boCIlYw0z\/mX63q\/WToG\/uufTGgdHNDybecaUa0\/FwQXvrZ3ZNB90zQ176ZfjEWvPQ\r\nsPbmcfADsILYTr6oPv8DTin85sdxStVe+yl0ZI07tXBJ8KQxfu0FXDUuTA+vBOUA4mxyg1Q8BY6m\r\nyADHxBv4RwvFMw18N8dMh6Np6bvB3BYf2xLyz4Pzp4WWq8ZATbeO6fT8OLNkpitJzwx6YQKYCNth\r\nV9+PByX6yVbrbA7niszimfSCuHPFmJSHHOczq2q7qLDoQuYjqfjbsLjjigaIT4zkd+XVE6PyPlHp\r\ng\/DKKUcy8gEItcHsxw38fJBzJP+5pJ26mN0WFXpvEskcRbYEtQ1HufWdxrbT7Mr7JrEsRIFjTeJL\r\nCKtRaW88\/BTU22IvDRHU+fQmEOzh1i0Bbg5Y82J2K0n6wKaRwIJ56L3h\/E6M9bbYy\/HwKoGbA4pm\r\n5H1RYems2AvudbuaUNRPtgYJmkQCW25xEQWWbI8IO3q0N46pr\/xIL27mN\/baQ3sICiE\/TXZUEgKQ\r\nNI5\/Wpiz9fX1maYJlbzyC3jWmeoCDEBSXo0BNqjU5EsQSSsNJ8d2U6SfQj6EJuZ8uiSQz46Y+y8i\r\nngZVIEhvIWWHOUcyCBIxK9rwzhaLVa5bJLJsR4dEEQn08GzrjcYeODzhjJp8tv2ssnfQjcGHomQT\r\n7+gW3NW9gVyiOiKOqNsjBTwkM70JrFmIQuAv6Ef7XJ7qjIHTzKOXANTcOyh828Gte+yfj5A30SPq\r\nqyStbBE2MuJDAgN5zvW6YgKiUyi5gvloQtQJ1o0SFGKhEYtFfElxD9Ve545QQvrgNy0191+AVHKt\r\ntWSDARwRoDkiwlEQMaqJfNTVNAQPo0FKQe54EHQPUdDjs+E6d5ASin9DN2z1mVudsk5Zp7zNlDBQ\r\nTsn\/JrdGadu2N1dZa9aC6778v1GSJOnNVVagKoc1Od6kXis1a8EcxxmG4UFKoPL5fBXKQCAgSZIH\r\nKQuFArBVKGOxWDab9SBlJpMBtgplNBotf2jgKUTgAV8CG3EvfWtQDftkMimKoqZpcOpUSQgC8TwP\r\nEpb\/xa+8zd0T38D8JcAA87pOsBMh4hsAAAAASUVORK5CYII=\r\n"
} ,
{
"id": "3",
"id_canal": "2",
"titulo": "Programa 1 canal 2",
"descripcion": "orem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived no",
"tipo": "Serie",
"inicio": "<?php $datetime = new DateTime(date('Y-m-d  12:00:00'));
echo $datetime->format('U') . '000'; ?>",
"fin": "<?php $datetime = new DateTime(date('Y-m-d  13:00:00'));
echo $datetime->format('U') . '000'; ?>",
"duracion": "60",
"file": "data:image\/gif;base64,iVBORw0KGgoAAAANSUhEUgAAADcAAAA3CAIAAAAnuUURAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJ\r\nbWFnZVJlYWR5ccllPAAAA3hpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw\/eHBhY2tldCBiZWdp\r\nbj0i77u\/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6\r\neD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMDY3IDc5LjE1\r\nNzc0NywgMjAxNS8wMy8zMC0yMzo0MDo0MiAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJo\r\ndHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlw\r\ndGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEu\r\nMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVz\r\nb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1N\r\nOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDo0MjdjM2JiNC05Njk4LTRjZTctYWYyOS1mNTZi\r\nMmFlMGVkYzgiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6QkZGQUEyNTJCMjQyMTFFNUFFODc5\r\nNTE2NENDMUU3MDMiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6QkZGQUEyNTFCMjQyMTFFNUFF\r\nODc5NTE2NENDMUU3MDMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUg\r\nKE1hY2ludG9zaCkiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlp\r\nZDo0MjdjM2JiNC05Njk4LTRjZTctYWYyOS1mNTZiMmFlMGVkYzgiIHN0UmVmOmRvY3VtZW50SUQ9\r\nInhtcC5kaWQ6NDI3YzNiYjQtOTY5OC00Y2U3LWFmMjktZjU2YjJhZTBlZGM4Ii8+IDwvcmRmOkRl\r\nc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+atCV\r\npQAABZtJREFUeNrsmluME1UYx+fMtZ1O2+22C6XLLisuLsomq3GFDctFNCEQJNEnEh4MolETH3wi\r\nxhijkUQNxvCiAYwxQAxREonxwsUgFxfEBwwuASOyYdldWrptp8tMZ6YznZkzfkObsl1WCArsoD2Z\r\nhzNnpjO\/+Z\/\/950zp4McxyE8X+hqzbbtVCqVy+WKxWKpVJoqIJZl\/X5\/LBZLJBIURZUbUVlLIOvv\r\n7w+FQvF4XBAEhmEQQncfEWBM01QUJZ1Oy7Lc1dUFxBVKKCdPngT25uZm7\/RyMpmEvu3u7ga9SNgX\r\nRRG09RQiFOABKmCDuksJXmxqavJg0ADVNUpVVcPhsAcpgQo8WqE0DIPjOA9SApWu6xVKy7JomvZi\r\nmqRpYKtQYoyrmclTBajKiZIk7oVSp7y9lND9MIh7EK4a1i4lDEEenxnVfVmnrFP+tyknTjKwY2QL\r\nh2FiX57h310YdDUtMk3BZSTi\/pZSKw3llePx8GqaCv7LG5pHRqj2BkcxqbmNt\/Az7FiOkhr7qlHo\r\n5dlZk\/Q4dnRROTazcW0V0cZ67RVKDoHHvUnZuCJ5pQH6obojL9ttnxGtY8kbvYtp5oQW47MzaMAE\r\nBiABnkm0zMgHEw3PlOslS\/w99bqk9UeDSzqbPwCaPy6\/nZUPIkS1Rte1xV4czG4dye+Ah1zYvo+m\r\nQqPS9+dHN9m4GOYfmdf8PkNF3Kca0+1h2T6dVZ47AHX26fbSnvPkNF7YvgJfVpX1B3BapTtjwher\r\n8MAV9dXDBEcJny6nOlztgQR4YoEV1\/c4pki+XBvIfOhnWro6tjiEO75fyu8yzNFFDxyy7MKvQ+vA\r\nOmnpmwWzv2boCIlYw0z\/mX63q\/WToG\/uufTGgdHNDybecaUa0\/FwQXvrZ3ZNB90zQ176ZfjEWvPQ\r\nsPbmcfADsILYTr6oPv8DTin85sdxStVe+yl0ZI07tXBJ8KQxfu0FXDUuTA+vBOUA4mxyg1Q8BY6m\r\nyADHxBv4RwvFMw18N8dMh6Np6bvB3BYf2xLyz4Pzp4WWq8ZATbeO6fT8OLNkpitJzwx6YQKYCNth\r\nV9+PByX6yVbrbA7niszimfSCuHPFmJSHHOczq2q7qLDoQuYjqfjbsLjjigaIT4zkd+XVE6PyPlHp\r\ng\/DKKUcy8gEItcHsxw38fJBzJP+5pJ26mN0WFXpvEskcRbYEtQ1HufWdxrbT7Mr7JrEsRIFjTeJL\r\nCKtRaW88\/BTU22IvDRHU+fQmEOzh1i0Bbg5Y82J2K0n6wKaRwIJ56L3h\/E6M9bbYy\/HwKoGbA4pm\r\n5H1RYems2AvudbuaUNRPtgYJmkQCW25xEQWWbI8IO3q0N46pr\/xIL27mN\/baQ3sICiE\/TXZUEgKQ\r\nNI5\/Wpiz9fX1maYJlbzyC3jWmeoCDEBSXo0BNqjU5EsQSSsNJ8d2U6SfQj6EJuZ8uiSQz46Y+y8i\r\nngZVIEhvIWWHOUcyCBIxK9rwzhaLVa5bJLJsR4dEEQn08GzrjcYeODzhjJp8tv2ssnfQjcGHomQT\r\n7+gW3NW9gVyiOiKOqNsjBTwkM70JrFmIQuAv6Ef7XJ7qjIHTzKOXANTcOyh828Gte+yfj5A30SPq\r\nqyStbBE2MuJDAgN5zvW6YgKiUyi5gvloQtQJ1o0SFGKhEYtFfElxD9Ve545QQvrgNy0191+AVHKt\r\ntWSDARwRoDkiwlEQMaqJfNTVNAQPo0FKQe54EHQPUdDjs+E6d5ASin9DN2z1mVudsk5Zp7zNlDBQ\r\nTsn\/JrdGadu2N1dZa9aC6778v1GSJOnNVVagKoc1Od6kXis1a8EcxxmG4UFKoPL5fBXKQCAgSZIH\r\nKQuFArBVKGOxWDab9SBlJpMBtgplNBotf2jgKUTgAV8CG3EvfWtQDftkMimKoqZpcOpUSQgC8TwP\r\nEpb\/xa+8zd0T38D8JcAA87pOsBMh4hsAAAAASUVORK5CYII=\r\n"
},
{
"id": "4",
"id_canal": "2",
"titulo": "Programa 2 canal 2",
"descripcion": "orem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived no",
"tipo": "Serie",
"inicio": "<?php $datetime = new DateTime(date('Y-m-d  13:00:00'));
echo $datetime->format('U') . '000'; ?>",
"fin": "<?php $datetime = new DateTime(date('Y-m-d  14:00:00'));
echo $datetime->format('U') . '000'; ?>",
"duracion": "60",
"file": "data:image\/gif;base64,iVBORw0KGgoAAAANSUhEUgAAADcAAAA3CAIAAAAnuUURAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJ\r\nbWFnZVJlYWR5ccllPAAAA3hpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw\/eHBhY2tldCBiZWdp\r\nbj0i77u\/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6\r\neD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMDY3IDc5LjE1\r\nNzc0NywgMjAxNS8wMy8zMC0yMzo0MDo0MiAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJo\r\ndHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlw\r\ndGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEu\r\nMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVz\r\nb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1N\r\nOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDo0MjdjM2JiNC05Njk4LTRjZTctYWYyOS1mNTZi\r\nMmFlMGVkYzgiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6QkZGQUEyNTJCMjQyMTFFNUFFODc5\r\nNTE2NENDMUU3MDMiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6QkZGQUEyNTFCMjQyMTFFNUFF\r\nODc5NTE2NENDMUU3MDMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUg\r\nKE1hY2ludG9zaCkiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlp\r\nZDo0MjdjM2JiNC05Njk4LTRjZTctYWYyOS1mNTZiMmFlMGVkYzgiIHN0UmVmOmRvY3VtZW50SUQ9\r\nInhtcC5kaWQ6NDI3YzNiYjQtOTY5OC00Y2U3LWFmMjktZjU2YjJhZTBlZGM4Ii8+IDwvcmRmOkRl\r\nc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+atCV\r\npQAABZtJREFUeNrsmluME1UYx+fMtZ1O2+22C6XLLisuLsomq3GFDctFNCEQJNEnEh4MolETH3wi\r\nxhijkUQNxvCiAYwxQAxREonxwsUgFxfEBwwuASOyYdldWrptp8tMZ6YznZkzfkObsl1WCArsoD2Z\r\nhzNnpjO\/+Z\/\/950zp4McxyE8X+hqzbbtVCqVy+WKxWKpVJoqIJZl\/X5\/LBZLJBIURZUbUVlLIOvv\r\n7w+FQvF4XBAEhmEQQncfEWBM01QUJZ1Oy7Lc1dUFxBVKKCdPngT25uZm7\/RyMpmEvu3u7ga9SNgX\r\nRRG09RQiFOABKmCDuksJXmxqavJg0ADVNUpVVcPhsAcpgQo8WqE0DIPjOA9SApWu6xVKy7JomvZi\r\nmqRpYKtQYoyrmclTBajKiZIk7oVSp7y9lND9MIh7EK4a1i4lDEEenxnVfVmnrFP+tyknTjKwY2QL\r\nh2FiX57h310YdDUtMk3BZSTi\/pZSKw3llePx8GqaCv7LG5pHRqj2BkcxqbmNt\/Az7FiOkhr7qlHo\r\n5dlZk\/Q4dnRROTazcW0V0cZ67RVKDoHHvUnZuCJ5pQH6obojL9ttnxGtY8kbvYtp5oQW47MzaMAE\r\nBiABnkm0zMgHEw3PlOslS\/w99bqk9UeDSzqbPwCaPy6\/nZUPIkS1Rte1xV4czG4dye+Ah1zYvo+m\r\nQqPS9+dHN9m4GOYfmdf8PkNF3Kca0+1h2T6dVZ47AHX26fbSnvPkNF7YvgJfVpX1B3BapTtjwher\r\n8MAV9dXDBEcJny6nOlztgQR4YoEV1\/c4pki+XBvIfOhnWro6tjiEO75fyu8yzNFFDxyy7MKvQ+vA\r\nOmnpmwWzv2boCIlYw0z\/mX63q\/WToG\/uufTGgdHNDybecaUa0\/FwQXvrZ3ZNB90zQ176ZfjEWvPQ\r\nsPbmcfADsILYTr6oPv8DTin85sdxStVe+yl0ZI07tXBJ8KQxfu0FXDUuTA+vBOUA4mxyg1Q8BY6m\r\nyADHxBv4RwvFMw18N8dMh6Np6bvB3BYf2xLyz4Pzp4WWq8ZATbeO6fT8OLNkpitJzwx6YQKYCNth\r\nV9+PByX6yVbrbA7niszimfSCuHPFmJSHHOczq2q7qLDoQuYjqfjbsLjjigaIT4zkd+XVE6PyPlHp\r\ng\/DKKUcy8gEItcHsxw38fJBzJP+5pJ26mN0WFXpvEskcRbYEtQ1HufWdxrbT7Mr7JrEsRIFjTeJL\r\nCKtRaW88\/BTU22IvDRHU+fQmEOzh1i0Bbg5Y82J2K0n6wKaRwIJ56L3h\/E6M9bbYy\/HwKoGbA4pm\r\n5H1RYems2AvudbuaUNRPtgYJmkQCW25xEQWWbI8IO3q0N46pr\/xIL27mN\/baQ3sICiE\/TXZUEgKQ\r\nNI5\/Wpiz9fX1maYJlbzyC3jWmeoCDEBSXo0BNqjU5EsQSSsNJ8d2U6SfQj6EJuZ8uiSQz46Y+y8i\r\nngZVIEhvIWWHOUcyCBIxK9rwzhaLVa5bJLJsR4dEEQn08GzrjcYeODzhjJp8tv2ssnfQjcGHomQT\r\n7+gW3NW9gVyiOiKOqNsjBTwkM70JrFmIQuAv6Ef7XJ7qjIHTzKOXANTcOyh828Gte+yfj5A30SPq\r\nqyStbBE2MuJDAgN5zvW6YgKiUyi5gvloQtQJ1o0SFGKhEYtFfElxD9Ve545QQvrgNy0191+AVHKt\r\ntWSDARwRoDkiwlEQMaqJfNTVNAQPo0FKQe54EHQPUdDjs+E6d5ASin9DN2z1mVudsk5Zp7zNlDBQ\r\nTsn\/JrdGadu2N1dZa9aC6778v1GSJOnNVVagKoc1Od6kXis1a8EcxxmG4UFKoPL5fBXKQCAgSZIH\r\nKQuFArBVKGOxWDab9SBlJpMBtgplNBotf2jgKUTgAV8CG3EvfWtQDftkMimKoqZpcOpUSQgC8TwP\r\nEpb\/xa+8zd0T38D8JcAA87pOsBMh4hsAAAAASUVORK5CYII=\r\n"
},
{
"id": "5",
"id_canal": "3",
"titulo": "Programa 1 canal 3",
"descripcion": "orem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived no",
"tipo": "Serie",
"inicio": "<?php $datetime = new DateTime(date('Y-m-d  12:00:00'));
echo $datetime->format('U') . '000'; ?>",
"fin": "<?php $datetime = new DateTime(date('Y-m-d  13:00:00'));
echo $datetime->format('U') . '000'; ?>",
"duracion": "30",
"file": "data:image\/gif;base64,iVBORw0KGgoAAAANSUhEUgAAADcAAAA3CAIAAAAnuUURAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJ\r\nbWFnZVJlYWR5ccllPAAAA3hpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw\/eHBhY2tldCBiZWdp\r\nbj0i77u\/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6\r\neD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMDY3IDc5LjE1\r\nNzc0NywgMjAxNS8wMy8zMC0yMzo0MDo0MiAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJo\r\ndHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlw\r\ndGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEu\r\nMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVz\r\nb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1N\r\nOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDo0MjdjM2JiNC05Njk4LTRjZTctYWYyOS1mNTZi\r\nMmFlMGVkYzgiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6QkZGQUEyNTJCMjQyMTFFNUFFODc5\r\nNTE2NENDMUU3MDMiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6QkZGQUEyNTFCMjQyMTFFNUFF\r\nODc5NTE2NENDMUU3MDMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUg\r\nKE1hY2ludG9zaCkiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlp\r\nZDo0MjdjM2JiNC05Njk4LTRjZTctYWYyOS1mNTZiMmFlMGVkYzgiIHN0UmVmOmRvY3VtZW50SUQ9\r\nInhtcC5kaWQ6NDI3YzNiYjQtOTY5OC00Y2U3LWFmMjktZjU2YjJhZTBlZGM4Ii8+IDwvcmRmOkRl\r\nc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+atCV\r\npQAABZtJREFUeNrsmluME1UYx+fMtZ1O2+22C6XLLisuLsomq3GFDctFNCEQJNEnEh4MolETH3wi\r\nxhijkUQNxvCiAYwxQAxREonxwsUgFxfEBwwuASOyYdldWrptp8tMZ6YznZkzfkObsl1WCArsoD2Z\r\nhzNnpjO\/+Z\/\/950zp4McxyE8X+hqzbbtVCqVy+WKxWKpVJoqIJZl\/X5\/LBZLJBIURZUbUVlLIOvv\r\n7w+FQvF4XBAEhmEQQncfEWBM01QUJZ1Oy7Lc1dUFxBVKKCdPngT25uZm7\/RyMpmEvu3u7ga9SNgX\r\nRRG09RQiFOABKmCDuksJXmxqavJg0ADVNUpVVcPhsAcpgQo8WqE0DIPjOA9SApWu6xVKy7JomvZi\r\nmqRpYKtQYoyrmclTBajKiZIk7oVSp7y9lND9MIh7EK4a1i4lDEEenxnVfVmnrFP+tyknTjKwY2QL\r\nh2FiX57h310YdDUtMk3BZSTi\/pZSKw3llePx8GqaCv7LG5pHRqj2BkcxqbmNt\/Az7FiOkhr7qlHo\r\n5dlZk\/Q4dnRROTazcW0V0cZ67RVKDoHHvUnZuCJ5pQH6obojL9ttnxGtY8kbvYtp5oQW47MzaMAE\r\nBiABnkm0zMgHEw3PlOslS\/w99bqk9UeDSzqbPwCaPy6\/nZUPIkS1Rte1xV4czG4dye+Ah1zYvo+m\r\nQqPS9+dHN9m4GOYfmdf8PkNF3Kca0+1h2T6dVZ47AHX26fbSnvPkNF7YvgJfVpX1B3BapTtjwher\r\n8MAV9dXDBEcJny6nOlztgQR4YoEV1\/c4pki+XBvIfOhnWro6tjiEO75fyu8yzNFFDxyy7MKvQ+vA\r\nOmnpmwWzv2boCIlYw0z\/mX63q\/WToG\/uufTGgdHNDybecaUa0\/FwQXvrZ3ZNB90zQ176ZfjEWvPQ\r\nsPbmcfADsILYTr6oPv8DTin85sdxStVe+yl0ZI07tXBJ8KQxfu0FXDUuTA+vBOUA4mxyg1Q8BY6m\r\nyADHxBv4RwvFMw18N8dMh6Np6bvB3BYf2xLyz4Pzp4WWq8ZATbeO6fT8OLNkpitJzwx6YQKYCNth\r\nV9+PByX6yVbrbA7niszimfSCuHPFmJSHHOczq2q7qLDoQuYjqfjbsLjjigaIT4zkd+XVE6PyPlHp\r\ng\/DKKUcy8gEItcHsxw38fJBzJP+5pJ26mN0WFXpvEskcRbYEtQ1HufWdxrbT7Mr7JrEsRIFjTeJL\r\nCKtRaW88\/BTU22IvDRHU+fQmEOzh1i0Bbg5Y82J2K0n6wKaRwIJ56L3h\/E6M9bbYy\/HwKoGbA4pm\r\n5H1RYems2AvudbuaUNRPtgYJmkQCW25xEQWWbI8IO3q0N46pr\/xIL27mN\/baQ3sICiE\/TXZUEgKQ\r\nNI5\/Wpiz9fX1maYJlbzyC3jWmeoCDEBSXo0BNqjU5EsQSSsNJ8d2U6SfQj6EJuZ8uiSQz46Y+y8i\r\nngZVIEhvIWWHOUcyCBIxK9rwzhaLVa5bJLJsR4dEEQn08GzrjcYeODzhjJp8tv2ssnfQjcGHomQT\r\n7+gW3NW9gVyiOiKOqNsjBTwkM70JrFmIQuAv6Ef7XJ7qjIHTzKOXANTcOyh828Gte+yfj5A30SPq\r\nqyStbBE2MuJDAgN5zvW6YgKiUyi5gvloQtQJ1o0SFGKhEYtFfElxD9Ve545QQvrgNy0191+AVHKt\r\ntWSDARwRoDkiwlEQMaqJfNTVNAQPo0FKQe54EHQPUdDjs+E6d5ASin9DN2z1mVudsk5Zp7zNlDBQ\r\nTsn\/JrdGadu2N1dZa9aC6778v1GSJOnNVVagKoc1Od6kXis1a8EcxxmG4UFKoPL5fBXKQCAgSZIH\r\nKQuFArBVKGOxWDab9SBlJpMBtgplNBotf2jgKUTgAV8CG3EvfWtQDftkMimKoqZpcOpUSQgC8TwP\r\nEpb\/xa+8zd0T38D8JcAA87pOsBMh4hsAAAAASUVORK5CYII=\r\n"
},
{
"id": "6",
"id_canal": "3",
"titulo": "Programa 2 canal 3",
"descripcion": "orem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived no",
"tipo": "Serie",
"inicio": "<?php $datetime = new DateTime(date('Y-m-d  13:00:00'));
echo $datetime->format('U') . '000'; ?>",
"fin": "<?php $datetime = new DateTime(date('Y-m-d  14:00:00'));
echo $datetime->format('U') . '000'; ?>",
"duracion": "60",
"file": "data:image\/gif;base64,iVBORw0KGgoAAAANSUhEUgAAADcAAAA3CAIAAAAnuUURAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJ\r\nbWFnZVJlYWR5ccllPAAAA3hpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw\/eHBhY2tldCBiZWdp\r\nbj0i77u\/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6\r\neD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMDY3IDc5LjE1\r\nNzc0NywgMjAxNS8wMy8zMC0yMzo0MDo0MiAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJo\r\ndHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlw\r\ndGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEu\r\nMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVz\r\nb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1N\r\nOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDo0MjdjM2JiNC05Njk4LTRjZTctYWYyOS1mNTZi\r\nMmFlMGVkYzgiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6QkZGQUEyNTJCMjQyMTFFNUFFODc5\r\nNTE2NENDMUU3MDMiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6QkZGQUEyNTFCMjQyMTFFNUFF\r\nODc5NTE2NENDMUU3MDMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUg\r\nKE1hY2ludG9zaCkiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlp\r\nZDo0MjdjM2JiNC05Njk4LTRjZTctYWYyOS1mNTZiMmFlMGVkYzgiIHN0UmVmOmRvY3VtZW50SUQ9\r\nInhtcC5kaWQ6NDI3YzNiYjQtOTY5OC00Y2U3LWFmMjktZjU2YjJhZTBlZGM4Ii8+IDwvcmRmOkRl\r\nc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+atCV\r\npQAABZtJREFUeNrsmluME1UYx+fMtZ1O2+22C6XLLisuLsomq3GFDctFNCEQJNEnEh4MolETH3wi\r\nxhijkUQNxvCiAYwxQAxREonxwsUgFxfEBwwuASOyYdldWrptp8tMZ6YznZkzfkObsl1WCArsoD2Z\r\nhzNnpjO\/+Z\/\/950zp4McxyE8X+hqzbbtVCqVy+WKxWKpVJoqIJZl\/X5\/LBZLJBIURZUbUVlLIOvv\r\n7w+FQvF4XBAEhmEQQncfEWBM01QUJZ1Oy7Lc1dUFxBVKKCdPngT25uZm7\/RyMpmEvu3u7ga9SNgX\r\nRRG09RQiFOABKmCDuksJXmxqavJg0ADVNUpVVcPhsAcpgQo8WqE0DIPjOA9SApWu6xVKy7JomvZi\r\nmqRpYKtQYoyrmclTBajKiZIk7oVSp7y9lND9MIh7EK4a1i4lDEEenxnVfVmnrFP+tyknTjKwY2QL\r\nh2FiX57h310YdDUtMk3BZSTi\/pZSKw3llePx8GqaCv7LG5pHRqj2BkcxqbmNt\/Az7FiOkhr7qlHo\r\n5dlZk\/Q4dnRROTazcW0V0cZ67RVKDoHHvUnZuCJ5pQH6obojL9ttnxGtY8kbvYtp5oQW47MzaMAE\r\nBiABnkm0zMgHEw3PlOslS\/w99bqk9UeDSzqbPwCaPy6\/nZUPIkS1Rte1xV4czG4dye+Ah1zYvo+m\r\nQqPS9+dHN9m4GOYfmdf8PkNF3Kca0+1h2T6dVZ47AHX26fbSnvPkNF7YvgJfVpX1B3BapTtjwher\r\n8MAV9dXDBEcJny6nOlztgQR4YoEV1\/c4pki+XBvIfOhnWro6tjiEO75fyu8yzNFFDxyy7MKvQ+vA\r\nOmnpmwWzv2boCIlYw0z\/mX63q\/WToG\/uufTGgdHNDybecaUa0\/FwQXvrZ3ZNB90zQ176ZfjEWvPQ\r\nsPbmcfADsILYTr6oPv8DTin85sdxStVe+yl0ZI07tXBJ8KQxfu0FXDUuTA+vBOUA4mxyg1Q8BY6m\r\nyADHxBv4RwvFMw18N8dMh6Np6bvB3BYf2xLyz4Pzp4WWq8ZATbeO6fT8OLNkpitJzwx6YQKYCNth\r\nV9+PByX6yVbrbA7niszimfSCuHPFmJSHHOczq2q7qLDoQuYjqfjbsLjjigaIT4zkd+XVE6PyPlHp\r\ng\/DKKUcy8gEItcHsxw38fJBzJP+5pJ26mN0WFXpvEskcRbYEtQ1HufWdxrbT7Mr7JrEsRIFjTeJL\r\nCKtRaW88\/BTU22IvDRHU+fQmEOzh1i0Bbg5Y82J2K0n6wKaRwIJ56L3h\/E6M9bbYy\/HwKoGbA4pm\r\n5H1RYems2AvudbuaUNRPtgYJmkQCW25xEQWWbI8IO3q0N46pr\/xIL27mN\/baQ3sICiE\/TXZUEgKQ\r\nNI5\/Wpiz9fX1maYJlbzyC3jWmeoCDEBSXo0BNqjU5EsQSSsNJ8d2U6SfQj6EJuZ8uiSQz46Y+y8i\r\nngZVIEhvIWWHOUcyCBIxK9rwzhaLVa5bJLJsR4dEEQn08GzrjcYeODzhjJp8tv2ssnfQjcGHomQT\r\n7+gW3NW9gVyiOiKOqNsjBTwkM70JrFmIQuAv6Ef7XJ7qjIHTzKOXANTcOyh828Gte+yfj5A30SPq\r\nqyStbBE2MuJDAgN5zvW6YgKiUyi5gvloQtQJ1o0SFGKhEYtFfElxD9Ve545QQvrgNy0191+AVHKt\r\ntWSDARwRoDkiwlEQMaqJfNTVNAQPo0FKQe54EHQPUdDjs+E6d5ASin9DN2z1mVudsk5Zp7zNlDBQ\r\nTsn\/JrdGadu2N1dZa9aC6778v1GSJOnNVVagKoc1Od6kXis1a8EcxxmG4UFKoPL5fBXKQCAgSZIH\r\nKQuFArBVKGOxWDab9SBlJpMBtgplNBotf2jgKUTgAV8CG3EvfWtQDftkMimKoqZpcOpUSQgC8TwP\r\nEpb\/xa+8zd0T38D8JcAA87pOsBMh4hsAAAAASUVORK5CYII=\r\n"
}]
}]
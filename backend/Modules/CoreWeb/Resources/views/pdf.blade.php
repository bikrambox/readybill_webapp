<?php
$array = ["<p>Hello</p>", "<script>alert('Hello');</script>"];

foreach ($data as $string) {
    echo htmlspecialchars($string) . "<br>";
}
?>
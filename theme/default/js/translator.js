/**
 * Created by Shavkat on 10/25/13.
 */
$(document).ready(function () {
    $('.translation').click(function () {
        return translateWord(this.innerHTML);
    })
})

function translateWord(_word) {
    alert('TODO translation: ' + _word);
}
const ques = document.getElementsByClassName('faq-question');
const divCount = ques.length;

// Assign IDs to all written questions, marked by the 'faq-question' class in the html
for (var i=0; i < divCount; i++) {
    document.getElementsByClassName('faq-question')[i].setAttribute("id", i);
};

function getDropContent(num) {
    // locate the id of the question the toggle belongs to
    let toggleTarget = document.getElementById(`${num}`);
    //toggle the 'show' style class onto '.faq-toggle-content'
    toggleTarget.querySelector(`.faq-toggle-content`).classList.toggle("show");
};

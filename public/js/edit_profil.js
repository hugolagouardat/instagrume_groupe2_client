var modalEditRuche = document.getElementById('editRuche');
modalEditRuche.addEventListener('show.bs.modal', function (event) {
    let rucheId = event.relatedTarget.getAttribute('data-ruche-id');

    let ajaxRequest = new XMLHttpRequest();
    ajaxRequest.open('GET', '/ruches/' + rucheId);
    ajaxRequest.send();

    ajaxRequest.addEventListener('readystatechange', function () {
        if (ajaxRequest.readyState === 4) {
            if (ajaxRequest.status === 200) {
                let rucheInfos = JSON.parse(ajaxRequest.responseText);

                modalEditRuche.querySelector('#inputEditId').value = rucheInfos.id;
                modalEditRuche.querySelector('#inputEditLibelle').value = rucheInfos.libelle;
            } else {
                console.log('Status error: ' + ajaxRequest.status);
            }
        }
    });
});

modalEditRuche.querySelector('#validRucheEdition').addEventListener('click', function (event) {
    let dataToSend = new Object();
    dataToSend.id = modalEditRuche.querySelector('#inputEditId').value;
    dataToSend.libelle = modalEditRuche.querySelector('#inputEditLibelle').value;

    let ajaxRequest = new XMLHttpRequest();
    ajaxRequest.addEventListener('readystatechange', function () {
        if (ajaxRequest.readyState === 4) {
            if (ajaxRequest.status === 200) {
                let rucheInfos = JSON.parse(ajaxRequest.response);

                let tabRuche = document.getElementById('tab-ruche-' + rucheInfos.id);
                tabRuche.children[0].innerHTML = rucheInfos.libelle;

                let modalBootstrap = bootstrap.Modal.getInstance(modalEditRuche);
                modalBootstrap.hide();
            } else {
                console.log('Status error: ' + ajaxRequest.status);
            }
        }
    });

    ajaxRequest.open('PUT', '/ruches');
    ajaxRequest.send(JSON.stringify(dataToSend));
});

function changePassword() {
    var currentPassword = document.getElementById('currentPassword').value;
    var newPassword = document.getElementById('newPassword').value;

    // Perform Ajax request
    var request = new XMLHttpRequest();
    request.open('POST', '/change-password', true);
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    request.onload = function () {
        if (request.status === 200) {
            document.getElementById('resultMessage').innerHTML = request.responseText;
        } else {
            console.error('Error:', request.statusText);
        }
    };
    request.send('currentPassword=' + currentPassword + '&newPassword=' + newPassword);
}
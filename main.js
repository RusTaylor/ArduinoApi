let changeValue = false;

init();
setInterval(() => {
    init(false);
}, 5000)

function init(createNewListeners = true) {
    if (!changeValue) {
        $.ajax({
            url: '/getData',
            type: 'POST',
            success: (res) => {
                console.log(res)
                renderData(res, createNewListeners);
            }
        })
    }
}

function renderData(response, createNewListeners = true) {
    let arduinoDatum = {}
    Object.keys(response).forEach((key) => {
        arduinoDatum[key] = {'monitor': [], 'control': []};

        response[key]['monitor'].forEach((monitorObject) => {
            arduinoDatum[key]['monitor'].push(renderMonitorRow(monitorObject))
        })

        response[key]['control'].forEach((monitorObject) => {
            arduinoDatum[key]['control'].push(renderControlRow(monitorObject, key, createNewListeners))
        })
    })

    let renderData = {};
    Object.keys(arduinoDatum).forEach((arduinoName) => {
        const monitorLength = arduinoDatum[arduinoName]['monitor'].length;
        const controlLength = arduinoDatum[arduinoName]['control'].length;
        let mainKey = '';
        let additionalKey = '';

        if (monitorLength > controlLength) {
            mainKey = 'monitor';
            additionalKey = 'control'
        } else if (monitorLength < controlLength) {
            mainKey = 'control';
            additionalKey = 'monitor'
        } else {
            mainKey = 'monitor';
            additionalKey = 'control'
        }

        renderData[arduinoName] = [];
        for (i = 0; i < arduinoDatum[arduinoName][mainKey].length; i++) {
            let mainString = arduinoDatum[arduinoName][mainKey][i];
            let additionalString = arduinoDatum[arduinoName][additionalKey][i] === undefined ? '' : arduinoDatum[arduinoName][additionalKey][i];
            let object = {}
            object[mainKey] = mainString;
            object[additionalKey] = additionalString;
            renderData[arduinoName].push(object)
        }
    })

    let body = ``;
    Object.keys(renderData).forEach((arduinoName) => {
        let content = ``;

        renderData[arduinoName].forEach((param) => {
            content += `<div class="row mt-2">
                        ${param['monitor']}
                        ${param['control']}
                        </div>`
        })

        body += `<div class="border mt-5">
        <div class="row justify-content-center mt-2">
            <span class="font-weight-bold">Имя ардуинки: </span><span class="font-italic">${arduinoName}</span>
        </div>
        ${content}
        </div>`
    })

    $('#mainContent').html(body);
}

function renderMonitorRow(monitorObject) {
    if (monitorObject['value'] === null || monitorObject['name'] === null) {
        return ``;
    }
    return `<div class="col-6 text-center">
                <span class="font-weight-bold">${monitorObject['name']}:</span>
                <span class="font-italic">${monitorObject['value']}</span>
            </div>`
}

function renderControlRow(monitorObject, arduinoName, createNewListeners = true) {
    if (monitorObject['value'] === null || monitorObject['name'] === null || monitorObject['type'] === null) {
        return ``;
    }

    let input = getInputByType(monitorObject, arduinoName, createNewListeners)

    return `<div class="col-6 text-center">
                <span class="font-weight-bold">${monitorObject['name']}: </span>
                ${input}
            </div>`
}

function getInputByType(monitorObject, arduinoName, createNewListeners = true) {
    if (monitorObject['type'] === 'inc_dec') {
        if(createNewListeners) {
            $(document).on('input', '#' + arduinoName + '_' + monitorObject['name'], (e) => {
                $('#' + arduinoName + '_' + monitorObject['name'] + "_value").text(e.target.value);
                changeValue = true;
            })

            $(document).on('click', '#' + arduinoName + '_' + monitorObject['name'] + '_send', (e) => {
                let value = $('#' + arduinoName + '_' + monitorObject['name']).val();
                $.ajax({
                    url: '/changeData',
                    type: 'POST',
                    data: {
                        id: arduinoName + '_' + monitorObject['name'] + '_send',
                        value: value
                    },
                    success: (res) => {
                        changeValue = false
                        init(false);
                    }
                })
            })
        }

        return `<input type="range" min="0" max="100" id="${arduinoName + '_' + monitorObject['name']}" value="${monitorObject['value']}">
                <span class="font-weight-bold">Значение:</span>
                <span class="font-italic" id="${arduinoName + '_' + monitorObject['name']}_value">${monitorObject['value']}</span>
                <button class="btn btn-sm btn-success" id="${arduinoName + '_' + monitorObject['name']}_send">Отправить</button>`
    }

    if (monitorObject['type'] === 'on_off') {
        let color = monitorObject['value'] == 0 ? 'btn-success' : 'btn-danger';
        let text = monitorObject['value'] == 0 ? 'Включить' : 'Выключить'
        let remoteValue = monitorObject['value'] == 0 ? 1 : 0

        if (createNewListeners) {
            $(document).on('click', '#' + arduinoName + '_' + monitorObject['name'] + '_btn', (e) => {
                $.ajax({
                    url: '/changeData',
                    type: 'POST',
                    data: {
                        id: arduinoName + '_' + monitorObject['name'] + '_btn',
                        value: $('#' + arduinoName + '_' + monitorObject['name'] + '_btn').data('value')
                    },
                    success: (res) => {
                        init(false);
                    }
                })
            })
        }

        return `<button class="btn btn-sm ${color}" id="${arduinoName + '_' + monitorObject['name']}_btn" data-value="${remoteValue}">${text}</button>`
    }
}
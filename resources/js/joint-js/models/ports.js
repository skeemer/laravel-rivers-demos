export const portsIn = {
    position: {
        name: 'left'
    },
    attrs: {
        portBody: {
            magnet: 'passive',
            r: 10,
            fill: '#023047',
            stroke: '#023047'
        }
    },
    label: {
        position: {
            name: 'left',
            args: { y: 6 }
        },
        markup: [{
            tagName: 'text',
            selector: 'label',
            className: 'label-text'
        }]
    },
    markup: [{
        tagName: 'circle',
        selector: 'portBody'
    }]
};

export const portsOut = {
    position: {
        name: 'right'
    },
    attrs: {
        portBody: {
            magnet: true,
            r: 10,
            fill: '#E6A502',
            stroke:'#023047'
        }
    },
    label: {
        position: {
            name: 'right',
            args: { x: -4, y: 1.5 }
        },
        markup: [{
            tagName: 'text',
            selector: 'label',
            className: 'label-text'
        }]
    },
    markup: [{
        tagName: 'circle',
        selector: 'portBody'
    }]
};

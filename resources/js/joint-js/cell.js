export default function cell(type, id, x, y, ports) {
    return {
        id,
        x,
        y,
        init() {
            const cell = this.addCell({type, id, x, y, ports});
            if (cell) {
                cell.listenTo(cell, 'change:position', (evt, position) => this.setPosition(position))
                cell.listenTo(cell, '', (evt, position) => this.setPosition(position))
                // if (type === 'fork') {
                //     function resizeFork() {
                //         cell.resize(
                //             cell.size().width,
                //             cell.prop('ports/items').filter(item => item.group === 'out').length * 50,
                //         )
                //     }
                //     cell.listenTo(cell, 'change:ports', resizeFork)
                //     resizeFork(cell, cell.attributes.ports)
                // }
            } else {
                this.getCell(id).prop('ports/items', [
                    {group: 'in'},
                    {group: 'out', id: `${id}-else`, attrs: {label: {text: 'E'}}},
                    ...ports.reverse().map((port, index) => ({
                        group: 'out',
                        id: port,
                        attrs: {label: {text: (index+1).toString()}},
                    })),
                ])
            }
        },
        setPosition(position) {
            this.x = position.x
            this.y = position.y
            this.$dispatch('moved')
        }
    }
}

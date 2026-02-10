export default function cell(type, id, x, y) {
    return {
        id,
        x,
        y,
        init() {
            const cell = this.addCell({type, id, x, y})
            cell.listenTo(cell, 'change:position', (evt, position) => this.setPosition(position))
        },
        setPosition(position) {
            this.x = position.x
            this.y = position.y
            this.$dispatch('moved')
        }
    }
}

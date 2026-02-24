export default function link(id, source, target, port) {
    return {
        init() {
            this.addLink(id, source, target, port)
        }
    }
}

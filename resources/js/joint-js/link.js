export default function link(id, source, target) {
    return {
        init() {
            this.addLink(id, source, target)
        }
    }
}

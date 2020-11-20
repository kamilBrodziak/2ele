// class Newsletter {
//     constructor(newsletter) {
//         this.form = newsletter.find('form');
//         this.inputs = this.form.find('input');
//     }
//
//     addLabelFromPlaceholder() {
//         let inputs = this.inputs;
//         inputs.each((i, el) => {
//             const input = $(el);
//             const label = input.parent();
//             const placeholder = input.attr('placeholder');
//             input.on('input', () => {
//                 let data = '';
//                 if(input.val() !== '') {
//                     data = placeholder;
//                 }
//                 label.attr('data-before', data);
//             })
//         })
//
//     }
//
// }